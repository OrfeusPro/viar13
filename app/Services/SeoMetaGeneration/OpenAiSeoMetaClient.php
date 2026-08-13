<?php

namespace App\Services\SeoMetaGeneration;

use App\Services\AltGeneration\Exceptions\InvalidOpenAiResponse;
use App\Services\AltGeneration\Exceptions\OpenAiApiException;
use App\Services\AltGeneration\Exceptions\OpenAiVisionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Psr\Log\LoggerInterface;

class OpenAiSeoMetaClient
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    /**
     * @var \GuzzleHttp\Client
     */
    private $http;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(ConfigRepository $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->http = new Client([
            'timeout' => (int) $this->config->get('seo_meta_generation.openai.timeout', 60),
        ]);
    }

    /**
     * @param array<int, string> $locales
     * @return array{locales: array<string, array{meta_title: string, meta_description: string}>, tokens: int|null, raw: array<string, mixed>}
     */
    public function generate(string $contextJson, array $locales, string $model): array
    {
        $apiKey = trim((string) $this->config->get('seo_meta_generation.openai.api_key', ''));

        if ($apiKey === '') {
            throw new OpenAiVisionException('OpenAI API key is not configured.');
        }

        $body = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt($contextJson, $locales),
                ],
            ],
            'response_format' => [
                'type' => 'json_object',
            ],
            'temperature' => 0.42,
            'presence_penalty' => 0.15,
            'frequency_penalty' => 0.15,
            'max_tokens' => (int) $this->config->get('seo_meta_generation.openai.max_tokens', 850),
        ];

        try {
            $response = $this->http->post(self::ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
                'timeout' => (int) $this->config->get('seo_meta_generation.openai.timeout', 60),
            ]);
        } catch (RequestException $exception) {
            $statusCode = $exception->getResponse() ? $exception->getResponse()->getStatusCode() : 0;
            $responseBody = $exception->getResponse() ? (string) $exception->getResponse()->getBody() : null;

            throw new OpenAiApiException(
                'OpenAI API request failed with status ' . $statusCode . '.',
                $statusCode,
                $responseBody,
                $exception
            );
        } catch (GuzzleException $exception) {
            throw new OpenAiApiException(
                'OpenAI API transport failed: ' . $exception->getMessage(),
                0,
                null,
                $exception
            );
        }

        $rawBody = (string) $response->getBody();
        $raw = json_decode($rawBody, true);

        if (!is_array($raw)) {
            throw new InvalidOpenAiResponse('OpenAI API returned invalid JSON.');
        }

        $content = $raw['choices'][0]['message']['content'] ?? null;

        if (!is_string($content) || trim($content) === '') {
            throw new InvalidOpenAiResponse('OpenAI API response did not include message content.');
        }

        $keywordMap = $this->keywordsByLocale($contextJson, $locales);
        $localesResult = $this->normalizeResponse($content, $locales);
        $localesResult = $this->ensureRequiredKeywords($localesResult, $keywordMap);

        return [
            'locales' => $localesResult,
            'tokens' => isset($raw['usage']['total_tokens']) ? (int) $raw['usage']['total_tokens'] : null,
            'raw' => $raw,
        ];
    }

    private function systemPrompt(): string
    {
        return 'You are a senior ecommerce SEO copywriter for ViarCanvas / Viar. '
            . 'You write commercial SEO meta titles and meta descriptions for wall art, canvas prints, portraits, gifts, articles, and service pages. '
            . 'Your job is to extract concrete selling points from the provided entity data and turn them into search-ready metadata. '
            . 'Use only provided facts. Preserve exact names, locations, product types, prices, formats, sizes, materials, and brand names when present. '
            . 'Admin supplied generation_keywords are approved campaign/SEO context for this exact row; they are allowed even when they are not present in the entity text. '
            . 'Do not invent discount amounts, delivery promises, guarantees, materials, sizes, personalization options, or prices. '
            . 'Never write generic AI marketing filler. Return JSON only.';
    }

    /**
     * @param array<int, string> $locales
     */
    private function userPrompt(string $contextJson, array $locales): string
    {
        return "Create strong SEO meta for each requested locale.\n"
            . "Output contract:\n"
            . "- Return exactly: {\"locales\":{\"<locale>\":{\"t\":\"...\",\"d\":\"...\"}}}\n"
            . "- t = meta title, max 60 characters; target 50-60 characters when source data allows it.\n"
            . "- d = meta description, max 155 characters; target 140-155 characters when source data allows it.\n"
            . "- Do not return a 90-110 character description if the entity provides enough concrete facts. Use the available space.\n"
            . "- Use the target locale language.\n\n"
            . "Priority order for source data:\n"
            . "1. If current meta_title/meta_description are present and useful, improve/compress them without losing commercial intent.\n"
            . "2. If current meta is missing, weak, duplicated, or generic, build from entity data: source_title, slug, source_description, body_excerpt, facts, page_type, product_type, primary_action, URL.\n"
            . "3. The source_title/name is usually the primary keyword. Keep it recognizable.\n"
            . "4. Use concrete facts from facts and source text: price, location, product type, style, format, material, size, occasion, article topic.\n"
            . "5. If a price such as 27€ or a value like price_from is provided, include it only when it is explicitly present.\n"
            . "6. REQUIRED KEYWORDS: if generation_keywords.phrases or generation_keywords.raw is present, include at least one supplied phrase exactly or nearly exactly in the final title or description. These phrases were supplied manually by an admin and are approved campaign/SEO context. Do not skip them just because they are absent from source_title/current_meta. If a phrase is a campaign term such as Black Friday, mention the campaign term without inventing a discount amount. Rewrite the description to fit the keyword within 155 characters. Do not keyword-stuff.\n\n"
            . "SEO title rules:\n"
            . "- Make it search-intent driven, not decorative.\n"
            . "- Aim for a full SERP-ready title: 50-60 characters. A shorter title is acceptable only when the keyword itself is short and no useful fact is available.\n"
            . "- For product/gallery items, prefer patterns like: Primary keyword + Product type + price/intent + Brand.\n"
            . "- For articles, prefer patterns like: Main topic + useful angle + Brand/blog if appropriate.\n"
            . "- Preserve important locations and proper names exactly.\n"
            . "- Do not replace a specific keyword with a vague phrase.\n"
            . "- If a supplied keyword fits naturally and the title remains readable, include the strongest keyword phrase in the title.\n"
            . "- If including the keyword in the title makes the title awkward, keep the title clean and include the keyword in the description instead.\n\n"
            . "Meta description rules:\n"
            . "- Must be specific and commercially useful.\n"
            . "- Aim for 140-155 characters. When the first draft is short, expand it with one more concrete fact: price, product type, order intent, sizes, format, location, article benefit, or brand.\n"
            . "- Do not pad with generic adjectives. Expand only with facts present in the context.\n"
            . "- For products: include action intent such as buy/order/create only if the entity is a product/service and context supports it.\n"
            . "- For articles: summarize the useful takeaway; do not pretend it is a product.\n"
            . "- Prefer concrete benefits from context over generic adjectives.\n"
            . "- If supplied keywords were not used in the title, include at least one supplied keyword phrase in the description.\n"
            . "- Avoid empty filler and buzzwords.\n\n"
            . "Banned generic phrases unless they are directly present in source text:\n"
            . "Discover high-quality, Perfect for, Elevate your space, stunning, beautiful addition, unique touch, enhance your home, personalized gifts, explore our collection, bring your memories to life.\n\n"
            . "Bad generic example:\n"
            . "Title: Merlion Park Canvas Art - ViarCanvas\n"
            . "Description: Discover high-quality canvas art of Merlion Park in Singapore. Perfect for personalized gifts!\n\n"
            . "Good product example when source contains name, canvas and price 27€:\n"
            . "Title: Merlion Park in Singapore Canvas from 27€ - ViarCanvas\n"
            . "Description: Buy Merlion Park in Singapore canvas from 27€ at ViarCanvas. Choose a size and order wall art online for home decor or a gift.\n\n"
            . "Good campaign keyword example when generation_keywords.raw is 'чёрная пятница':\n"
            . "Title: Портрет Симпсонов на холсте от 50€ - ViarCanvas\n"
            . "Description: Чёрная пятница: закажите портрет Симпсонов на холсте от 50€. Яркий подарок с разными размерами на выбор от ViarCanvas.\n\n"
            . "Good article example when no product price exists:\n"
            . "Title: How to Choose a Canvas Photo Size - ViarCanvas\n"
            . "Description: Learn how to choose the right canvas photo size for your wall, image quality and room layout before ordering a custom print.\n\n"
            . 'Locales: ' . implode(',', $locales) . "\n"
            . 'Context JSON: ' . $contextJson;
    }

    /**
     * @param array<int, string> $requestedLocales
     * @return array<string, array{meta_title: string, meta_description: string}>
     */
    private function normalizeResponse(string $content, array $requestedLocales): array
    {
        $content = trim($content);

        if (strpos($content, '```') === 0) {
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content);
            $content = trim((string) $content);
        }

        $decoded = json_decode($content, true);

        if (!is_array($decoded) || !isset($decoded['locales']) || !is_array($decoded['locales'])) {
            $this->logger->warning('Invalid OpenAI SEO meta response shape.', ['content' => $content]);
            throw new InvalidOpenAiResponse('OpenAI generated SEO meta content was not valid JSON.');
        }

        $result = [];

        foreach ($requestedLocales as $locale) {
            if (!isset($decoded['locales'][$locale]) || !is_array($decoded['locales'][$locale])) {
                continue;
            }

            $title = $this->truncateGeneratedText((string) ($decoded['locales'][$locale]['t'] ?? ''), 'meta_title_max');
            $description = $this->truncateGeneratedText((string) ($decoded['locales'][$locale]['d'] ?? ''), 'meta_description_max');

            if ($title === '' && $description === '') {
                continue;
            }

            $result[$locale] = [
                'meta_title' => $title,
                'meta_description' => $description,
            ];
        }

        if (empty($result)) {
            throw new InvalidOpenAiResponse('OpenAI response did not include usable SEO meta values.');
        }

        return $result;
    }

    /**
     * @param array<int, string> $locales
     * @return array<string, array<int, string>>
     */
    private function keywordsByLocale(string $contextJson, array $locales): array
    {
        $decoded = json_decode($contextJson, true);
        $result = [];

        if (!is_array($decoded)) {
            return $result;
        }

        $globalKeywords = $this->extractKeywordPhrases($decoded['generation_keywords'] ?? null);

        foreach ($locales as $locale) {
            $localeKeywords = [];

            if (isset($decoded['locales'][$locale]) && is_array($decoded['locales'][$locale])) {
                $localeKeywords = $this->extractKeywordPhrases($decoded['locales'][$locale]['generation_keywords'] ?? null);
            }

            $phrases = !empty($localeKeywords) ? $localeKeywords : $globalKeywords;

            if (!empty($phrases)) {
                $result[$locale] = $phrases;
            }
        }

        return $result;
    }

    /**
     * @param mixed $keywordContext
     * @return array<int, string>
     */
    private function extractKeywordPhrases($keywordContext): array
    {
        if (!is_array($keywordContext)) {
            return [];
        }

        $phrases = [];

        if (isset($keywordContext['phrases']) && is_array($keywordContext['phrases'])) {
            foreach ($keywordContext['phrases'] as $phrase) {
                if (is_string($phrase)) {
                    $phrases[] = $phrase;
                }
            }
        }

        if (isset($keywordContext['raw']) && is_string($keywordContext['raw'])) {
            $parts = preg_split('/[\r\n,;]+/u', $keywordContext['raw']);

            if (is_array($parts)) {
                foreach ($parts as $part) {
                    $phrases[] = $part;
                }
            }
        }

        $clean = [];
        $seen = [];

        foreach ($phrases as $phrase) {
            $phrase = trim((string) preg_replace('/\s+/u', ' ', $phrase));

            if ($phrase === '') {
                continue;
            }

            $key = $this->lower($phrase);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $clean[] = $phrase;
        }

        return $clean;
    }

    /**
     * @param array<string, array{meta_title: string, meta_description: string}> $result
     * @param array<string, array<int, string>> $keywordMap
     * @return array<string, array{meta_title: string, meta_description: string}>
     */
    private function ensureRequiredKeywords(array $result, array $keywordMap): array
    {
        foreach ($keywordMap as $locale => $phrases) {
            if (!isset($result[$locale]) || empty($phrases)) {
                continue;
            }

            $title = (string) ($result[$locale]['meta_title'] ?? '');
            $description = (string) ($result[$locale]['meta_description'] ?? '');

            if ($this->containsAnyKeyword($title . ' ' . $description, $phrases)) {
                continue;
            }

            $keyword = $this->firstKeywordThatFits($phrases);

            if ($keyword === null) {
                continue;
            }

            $result[$locale] = $this->forceKeywordIntoMeta($title, $description, $keyword);
        }

        return $result;
    }

    /**
     * @param array<int, string> $phrases
     */
    private function containsAnyKeyword(string $haystack, array $phrases): bool
    {
        $haystack = $this->lower($haystack);

        foreach ($phrases as $phrase) {
            if ($phrase === '') {
                continue;
            }

            if (strpos($haystack, $this->lower($phrase)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $phrases
     */
    private function firstKeywordThatFits(array $phrases): ?string
    {
        $descriptionMax = (int) $this->config->get('seo_meta_generation.limits.meta_description_max', 155);
        $titleMax = (int) $this->config->get('seo_meta_generation.limits.meta_title_max', 60);
        $max = max($descriptionMax, $titleMax);

        foreach ($phrases as $phrase) {
            $phrase = trim($phrase);

            if ($phrase !== '' && $this->length($phrase) <= $max) {
                return $phrase;
            }
        }

        return null;
    }

    /**
     * @return array{meta_title: string, meta_description: string}
     */
    private function forceKeywordIntoMeta(string $title, string $description, string $keyword): array
    {
        $descriptionMax = (int) $this->config->get('seo_meta_generation.limits.meta_description_max', 155);
        $titleMax = (int) $this->config->get('seo_meta_generation.limits.meta_title_max', 60);
        $keyword = trim((string) preg_replace('/\s+/u', ' ', $keyword));

        if ($keyword === '') {
            return [
                'meta_title' => $this->truncateGeneratedText($title, 'meta_title_max'),
                'meta_description' => $this->truncateGeneratedText($description, 'meta_description_max'),
            ];
        }

        $title = $this->truncateGeneratedText($title, 'meta_title_max');
        $description = $this->truncateGeneratedText($description, 'meta_description_max');

        if ($description === '') {
            $description = $this->truncate($keyword, $descriptionMax);
        } else {
            $separator = $this->endsWithSentencePunctuation($description) ? ' ' : '. ';
            $suffix = $separator . $keyword;

            if (!$this->endsWithSentencePunctuation($keyword)) {
                $suffix .= '.';
            }

            if ($this->length($description . $suffix) <= $descriptionMax) {
                $description .= $suffix;
            } else {
                $baseLimit = $descriptionMax - $this->length($suffix);

                if ($baseLimit >= 25) {
                    $description = rtrim($this->truncate($description, $baseLimit), " .,!?:;\t\n\r\0\x0B") . $suffix;
                } else {
                    $prefix = $keyword . ': ';
                    $description = $prefix . $this->truncate($description, max(0, $descriptionMax - $this->length($prefix)));
                }
            }
        }

        if ($title === '') {
            $title = $this->truncate($keyword . ' - ViarCanvas', $titleMax);
        }

        return [
            'meta_title' => $title,
            'meta_description' => $this->truncateGeneratedText($description, 'meta_description_max'),
        ];
    }

    private function lower(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    private function truncate(string $value, int $limit): string
    {
        if ($limit <= 0) {
            return '';
        }

        $value = trim((string) preg_replace('/\s+/u', ' ', $value));

        if ($this->length($value) <= $limit) {
            return $value;
        }

        return function_exists('mb_substr') ? mb_substr($value, 0, $limit, 'UTF-8') : substr($value, 0, $limit);
    }

    private function endsWithSentencePunctuation(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        $last = function_exists('mb_substr') ? mb_substr($value, -1, 1, 'UTF-8') : substr($value, -1);

        return in_array($last, ['.', '!', '?', ':', ';'], true);
    }

    private function truncateGeneratedText(string $value, string $limitKey): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        $limit = (int) $this->config->get('seo_meta_generation.limits.' . $limitKey, 0);

        if ($limit <= 0) {
            return $value;
        }

        if (function_exists('mb_strlen') && mb_strlen($value, 'UTF-8') > $limit) {
            return mb_substr($value, 0, $limit, 'UTF-8');
        }

        if (!function_exists('mb_strlen') && strlen($value) > $limit) {
            return substr($value, 0, $limit);
        }

        return $value;
    }
}
