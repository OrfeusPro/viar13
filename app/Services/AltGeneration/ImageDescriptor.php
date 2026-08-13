<?php

namespace App\Services\AltGeneration;

/**
 * Describes an image discovered on an Eloquent model or inside HTML content.
 */
class ImageDescriptor
{
    /**
     * Normalized image path or remote URL.
     *
     * @var string
     */
    public $path;

    /**
     * Source model column.
     *
     * @var string|null
     */
    public $field;

    /**
     * Existing alt text from HTML, when available.
     *
     * @var string|null
     */
    public $currentAlt;

    /**
     * Existing title text from HTML, when available.
     *
     * @var string|null
     */
    public $currentTitle;

    /**
     * Discovery source: field, html, or media.
     *
     * @var string
     */
    public $sourceType;

    /**
     * XPath for HTML images so a later writer can update the exact node.
     *
     * @var string|null
     */
    public $xpath;

    /**
     * Local absolute filesystem path, when resolvable.
     *
     * @var string|null
     */
    public $absolutePath;

    /**
     * Public image URL, when resolvable.
     *
     * @var string|null
     */
    public $publicUrl;

    /**
     * Extra source metadata kept in prompt_context.
     *
     * @var array<string, mixed>
     */
    public $meta;

    /**
     * @param string $path
     * @param string|null $field
     * @param string|null $currentAlt
     * @param string|null $currentTitle
     * @param string $sourceType
     * @param string|null $xpath
     * @param string|null $absolutePath
     * @param string|null $publicUrl
     * @param array<string, mixed> $meta
     */
    public function __construct(
        string $path,
        ?string $field,
        ?string $currentAlt,
        ?string $currentTitle,
        string $sourceType,
        ?string $xpath = null,
        ?string $absolutePath = null,
        ?string $publicUrl = null,
        array $meta = []
    ) {
        $this->path = $path;
        $this->field = $field;
        $this->currentAlt = $currentAlt;
        $this->currentTitle = $currentTitle;
        $this->sourceType = $sourceType;
        $this->xpath = $xpath;
        $this->absolutePath = $absolutePath;
        $this->publicUrl = $publicUrl;
        $this->meta = $meta;
    }

    /**
     * Convert the descriptor into a compact array for prompt context storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge([
            'path' => $this->path,
            'field' => $this->field,
            'current_alt' => $this->currentAlt,
            'current_title' => $this->currentTitle,
            'source_type' => $this->sourceType,
            'xpath' => $this->xpath,
            'public_url' => $this->publicUrl,
        ], $this->meta);
    }
}
