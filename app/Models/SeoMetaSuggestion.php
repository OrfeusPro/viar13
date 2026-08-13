<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use InvalidArgumentException;

/**
 * Stores generated SEO meta title/description suggestions pending Voyager review.
 */
class SeoMetaSuggestion extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_GENERATED = 'generated';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_GENERATED,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_APPLIED,
        self::STATUS_REJECTED,
        self::STATUS_FAILED,
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'metaable_type',
        'metaable_id',
        'locale',
        'page_url',
        'entity_label',
        'entity_title',
        'seo_keywords',
        'title_field',
        'description_field',
        'current_meta_title',
        'current_meta_description',
        'suggested_meta_title',
        'suggested_meta_description',
        'approved_meta_title',
        'approved_meta_description',
        'status',
        'prompt_context',
        'model',
        'tokens_used',
        'error',
        'generated_at',
        'reviewed_at',
        'reviewed_by',
        'applied_at',
        'applied_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'prompt_context' => 'array',
        'generated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'applied_at' => 'datetime',
    ];

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return self::STATUSES;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Unknown SEO meta suggestion status: ' . $status);
        }

        $this->setAttribute('status', $status);

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function metaable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function applier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
