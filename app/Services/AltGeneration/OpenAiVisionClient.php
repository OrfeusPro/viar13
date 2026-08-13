<?php

namespace App\Services\AltGeneration;

use App\Services\AltGeneration\Exceptions\ImagePayloadException;
use App\Services\AltGeneration\Exceptions\InvalidOpenAiResponse;
use App\Services\AltGeneration\Exceptions\OpenAiApiException;
use App\Services\AltGeneration\Exceptions\OpenAiVisionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Psr\Log\LoggerInterface;

/**
 * Thin OpenAI Chat Completions client for image alt/title generation.
 */
class OpenAiVisionClient
{
    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    private const USER_INSTRUCTION = 'Generate SEO-aware image alt and title text for this exact page placement. '
    . 'The image must be described in connection with the product/service/page context, not as an isolated image. '
    . 'The alt text MUST include the primary page/product context when available. '
    . 'If dimensions, size, format, material, product type, or service name are present in seo_context.required_terms, preserve them exactly when relevant and within limits. '
    . 'Do not produce a purely visual caption when page context identifies what the image is for. '
    . 'Visible content should support the context, but page/product context has priority for SEO meaning. '
    . 'Do not invent details that are neither visible nor present in context. '
    . 'Language MUST match `language`. Alt <= 125 chars, Title <= 70 chars. '
    . 'If page_context.visible_page_text or page_context.translated_context exists, treat it as the strongest local context for this exact image. '
    . 'Use nearby page text to describe why this image is used, not only what object is visible. '
    . 'No keyword stuffing. Do not repeat wording from sibling_image_alts.';

    /**
     * @var \GuzzleHttp\Client
     */
    private $http;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    private $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \GuzzleHttp\Client $http
     * @param string $apiKey
     * @param int $timeout
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Client $http,
        string $apiKey,
        int $timeout,
        ConfigRepository $config,
        LoggerInterface $logger
    ) {
        $this->http = $http;
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Generate SEO alt and title text using the configured OpenAI endpoint.
     *
     * @param array<string, mixed> $payload
     * @param string $model
     * @return array{alt: string, title: string, tokens: int|null, raw: array<string, mixed>}
     *
     * @throws \App\Services\AltGeneration\Exceptions\OpenAiVisionException
     */
    public function generate(array $payload, string $model): array
    {
        if (trim($this->apiKey) === '') {
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
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $this->userPrompt($payload),
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $this->imageUrl($payload),
                                'detail' => 'low',
                            ],
                        ],
                    ],
                ],
            ],
            'response_format' => [
                'type' => 'json_object',
            ],
            'temperature' => $this->hasPreviousGeneration($payload) ? 0.85 : 0.45,
            'presence_penalty' => $this->hasPreviousGeneration($payload) ? 0.4 : 0,
            'frequency_penalty' => $this->hasPreviousGeneration($payload) ? 0.4 : 0,
            'max_tokens' => 160,
        ];

        try {
            $response = $this->http->post(self::ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
                'timeout' => $this->timeout,
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

        $statusCode = $response->getStatusCode();
        $rawBody = (string) $response->getBody();

        if ($statusCode >= 400) {
            throw new OpenAiApiException(
                'OpenAI API request failed with status ' . $statusCode . '.',
                $statusCode,
                $rawBody
            );
        }

        $raw = json_decode($rawBody, true);

        if (!is_array($raw)) {
            throw new InvalidOpenAiResponse('OpenAI API returned invalid JSON.');
        }

        $content = $raw['choices'][0]['message']['content'] ?? null;

        if (!is_string($content) || trim($content) === '') {
            throw new InvalidOpenAiResponse('OpenAI API response did not include message content.');
        }

        $decoded = $this->decodeGeneratedJson($content);
        $alt = $this->truncateGeneratedText((string) ($decoded['alt'] ?? ''), 'alt_max');
        $title = $this->truncateGeneratedText((string) ($decoded['title'] ?? ''), 'title_max');

        if ($alt === '' && $title === '') {
            throw new InvalidOpenAiResponse('OpenAI API response did not include alt or title values.');
        }

        return [
            'alt' => $alt,
            'title' => $title,
            'tokens' => isset($raw['usage']['total_tokens']) ? (int) $raw['usage']['total_tokens'] : null,
            'raw' => $raw,
        ];
    }

    /**
     * @return string
     */
    private function systemPrompt(): string
    {
        $projectContext = trim((string) $this->config->get('alt_generation.project_context', ''));
        $rules = (array) $this->config->get('alt_generation.rules', []);
        $lines = [];

        if ($projectContext !== '') {
            $lines[] = $projectContext;
        }

        $lines[] = 'Generate accurate SEO image alt and title text for a multilingual ecommerce/admin workflow.';
        $lines[] = 'Return only a valid JSON object with keys "alt" and "title".';

        if (!empty($rules)) {
            $lines[] = 'Rules:';

            foreach ($rules as $rule) {
                if (is_string($rule) && trim($rule) !== '') {
                    $lines[] = '- ' . trim($rule);
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, mixed> $payload
     * @return string
     */
    private function userPrompt(array $payload): string
    {
        $image = $payload['image'] ?? [];
        $context = isset($payload['context']) && is_array($payload['context'])
            ? $payload['context']
            : [];
        $language = isset($context['language']) && is_string($context['language'])
            ? $context['language']
            : 'the page language';
        $contextJson = json_encode([
            'seo_context' => $this->promptSeoContext($payload),
            'page_context' => $this->promptPageContext($payload),
            'image' => $image,
            'generation' => $this->promptGenerationContext($payload),
            'limits' => $this->config->get('alt_generation.limits', []),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        if ($contextJson === false) {
            $contextJson = '{}';
        }

        return "Create one SEO-focused alt text and one title text for this image.\n"
            . "Target language/locale: " . $language . ".\n"
            . self::USER_INSTRUCTION . "\n"
            . "Important priority order:\n"
            . "1. Use seo_context.primary_context as the main SEO meaning of the image.\n"
            . "2. Include seo_context.required_terms when they are present, especially exact dimensions like 100х70 см.\n"
            . "3. If page_context.visible_page_text or page_context.translated_context is present, use that nearby text as the exact local meaning of this image.\n"
            . "4. Add a short visible-image detail only after the local page context is clear.\n"
            . "5. Do not return only a description of people, clothes, colors, background, damage, or objects if nearby page text explains the condition, service, guarantee, delivery, product, or page block.\n"
            . "6. For Russian pages, write natural Russian commercial SEO text.\n"
            . "Good Russian example for similar context: \"Механические повреждения холста, подрамника или упаковки при доставке\".\n"
            . "Bad example: \"Деталь холста, показывающая заднюю часть картины\" because it ignores the nearby page text.\n"
            . "If generation.mode is \"regenerate\", create a noticeably different alternative from generation.previous_alt and generation.previous_title.\n"
            . "Do not repeat the previous alt/title exactly or with only minor word changes.\n"
            . "Keep the same SEO meaning and required terms, but vary the wording, focus, and visible detail.\n"
            . "If the previous version focused on people/clothes, shift focus toward the product/service/page context, size, material, format, or finished result when supported by context.\n"
            . "Return only JSON with string keys \"alt\" and \"title\".\n"
            . "Context JSON:\n```json\n" . $contextJson . "\n```";
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function promptPageContext(array $payload): array
    {
        $context = isset($payload['context']) && is_array($payload['context'])
            ? $payload['context']
            : [];
        $keys = [
            'page_title',
            'page_url',
            'section_block',
            'category',
            'purpose',
            'surrounding_text',
            'visible_page_text',
            'translated_context',
            'placement_context',
            'position_name',
            'page_url_status',
            'resolver_source',
            'language',
            'current_alt',
            'current_title',
            'sibling_image_alts',
        ];
        $filtered = [];

        foreach ($keys as $key) {
            $filtered[$key] = $context[$key] ?? null;
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $payload
     * @return string
     */
    private function imageUrl(array $payload): string
    {
        $publicUrl = isset($payload['public_url']) ? (string) $payload['public_url'] : '';
        $absolutePath = isset($payload['absolute_path']) ? (string) $payload['absolute_path'] : '';
        $path = isset($payload['path']) ? (string) $payload['path'] : '';

        if ($publicUrl !== '' && $this->isPublicHttpUrl($publicUrl)) {
            return $publicUrl;
        }

        if ($absolutePath !== '' && is_file($absolutePath) && is_readable($absolutePath)) {
            return $this->dataUrlFromFile($absolutePath);
        }

        if (strpos($path, 'data:image/') === 0) {
            return $path;
        }

        if ($publicUrl !== '' && preg_match('#^https?://#i', $publicUrl)) {
            return $publicUrl;
        }

        if ($path !== '' && preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $this->logger->warning('Unable to resolve image payload for OpenAI Vision.', [
            'path' => $path,
            'public_url' => $publicUrl,
            'absolute_path' => $absolutePath,
        ]);

        throw new ImagePayloadException('Image cannot be resolved to a public URL or readable local file.');
    }

    /**
     * @param string $url
     * @return bool
     */
    private function isPublicHttpUrl(string $url): bool
    {
        if (!preg_match('#^https?://#i', $url)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
            return false;
        }

        return substr($host, -5) !== '.test';
    }

    /**
     * @param string $path
     * @return string
     */
    private function dataUrlFromFile(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new ImagePayloadException('Unable to read image file.');
        }

        return 'data:' . $this->mimeType($path) . ';base64,' . base64_encode($contents);
    }

    /**
     * @param string $path
     * @return string
     */
    private function mimeType(string $path): string
    {
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($path);

            if (is_string($mimeType) && strpos($mimeType, 'image/') === 0) {
                return $mimeType;
            }
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
        ];

        return $map[$extension] ?? 'image/jpeg';
    }

    /**
     * @param string $content
     * @return array<string, mixed>
     */
    private function decodeGeneratedJson(string $content): array
    {
        $content = trim($content);

        if (strpos($content, '```') === 0) {
            $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content);
            $content = trim((string) $content);
        }

        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new InvalidOpenAiResponse('OpenAI generated content was not valid JSON.');
        }

        return $decoded;
    }

    /**
     * @param string $value
     * @param string $limitKey
     * @return string
     */
    private function truncateGeneratedText(string $value, string $limitKey): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        $limit = (int) $this->config->get('alt_generation.limits.' . $limitKey, 0);

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

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function promptSeoContext(array $payload): array
    {
        $context = isset($payload['context']) && is_array($payload['context'])
            ? $payload['context']
            : [];

        $pageTitle = trim((string) ($context['page_title'] ?? ''));
        $purpose = trim((string) ($context['purpose'] ?? ''));
        $surroundingText = trim((string) ($context['surrounding_text'] ?? ''));
        $visiblePageText = trim((string) ($context['visible_page_text'] ?? ''));
        $translatedContext = trim((string) ($context['translated_context'] ?? ''));
        $localContext = $translatedContext !== '' ? $translatedContext : $visiblePageText;

        $primaryContext = $localContext !== '' ? $localContext : ($pageTitle !== '' ? $pageTitle : $purpose);

        $requiredTerms = [];

        if ($primaryContext !== '') {
            $requiredTerms[] = $primaryContext;
        }

        foreach ($this->extractDimensions($surroundingText . ' ' . $localContext) as $dimension) {
            $requiredTerms[] = $dimension;
        }

        $requiredTerms = $this->uniqueNonEmpty($requiredTerms);

        return [
            'primary_context' => $primaryContext,
            'required_terms' => $requiredTerms,
            'local_image_context' => $localContext,
            'surrounding_text' => $surroundingText,
            'instruction' => 'Alt text must combine the exact nearby page text with visible image details. Do not generate a visual-only caption.',
        ];
    }

    /**
     * @param string $text
     * @return array<int, string>
     */
    private function extractDimensions(string $text): array
    {
        $dimensions = [];

        if (preg_match_all('/\b\d{2,3}\s*[xх×]\s*\d{2,3}\s*(?:см|cm|мм|mm)?\b/ui', $text, $matches)) {
            foreach ($matches[0] as $match) {
                $dimensions[] = trim((string) preg_replace('/\s+/u', ' ', $match));
            }
        }

        return $this->uniqueNonEmpty($dimensions);
    }

    /**
     * @param array<int, mixed> $values
     * @return array<int, string>
     */
    private function uniqueNonEmpty(array $values): array
    {
        $result = [];
        $seen = [];

        foreach ($values as $value) {
            $value = trim((string) preg_replace('/\s+/u', ' ', (string) $value));

            if ($value === '') {
                continue;
            }

            $key = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function promptGenerationContext(array $payload): array
    {
        $generation = isset($payload['generation']) && is_array($payload['generation'])
            ? $payload['generation']
            : [];

        return [
            'mode' => $generation['mode'] ?? 'generate',
            'previous_alt' => $generation['previous_alt'] ?? null,
            'previous_title' => $generation['previous_title'] ?? null,
            'variation_seed' => $generation['variation_seed'] ?? null,
            'instruction' => 'For regenerate mode, produce a different valid SEO alternative and do not repeat the previous result.',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return bool
     */
    private function hasPreviousGeneration(array $payload): bool
    {
        $generation = isset($payload['generation']) && is_array($payload['generation'])
            ? $payload['generation']
            : [];

        return trim((string) ($generation['previous_alt'] ?? '')) !== ''
            || trim((string) ($generation['previous_title'] ?? '')) !== '';
    }
}
