<?php

namespace App\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Stores generated image alt/title suggestions pending Voyager review.
 *
 * @property int $id
 * @property string|null $imageable_type
 * @property int|null $imageable_id
 * @property string|null $field
 * @property string $image_path
 * @property string|null $locale
 * @property string|null $image_hash
 * @property string|null $page_url
 * @property string|null $current_alt
 * @property string|null $current_title
 * @property string|null $suggested_alt
 * @property string|null $suggested_title
 * @property string|null $approved_alt
 * @property string|null $approved_title
 * @property string $status
 * @property array|null $prompt_context
 * @property string|null $model
 * @property int|null $tokens_used
 * @property string|null $error
 * @property \Illuminate\Support\Carbon|null $generated_at
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $applied_at
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ImageAltSuggestion extends Model
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

    private const TRANSITIONS = [
        self::STATUS_NEW => [
            self::STATUS_NEW,
            self::STATUS_GENERATED,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_FAILED,
        ],
        self::STATUS_GENERATED => [
            self::STATUS_GENERATED,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_APPLIED,
            self::STATUS_REJECTED,
            self::STATUS_FAILED,
            self::STATUS_NEW,
        ],
        self::STATUS_PENDING => [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_FAILED,
            self::STATUS_NEW,
        ],
        self::STATUS_APPROVED => [
            self::STATUS_APPROVED,
            self::STATUS_APPLIED,
            self::STATUS_REJECTED,
            self::STATUS_FAILED,
            self::STATUS_NEW,
        ],
        self::STATUS_APPLIED => [
            self::STATUS_APPLIED,
            self::STATUS_APPROVED,
            self::STATUS_FAILED,
            self::STATUS_NEW,
        ],
        self::STATUS_REJECTED => [
            self::STATUS_REJECTED,
            self::STATUS_APPROVED,
            self::STATUS_FAILED,
            self::STATUS_NEW,
        ],
        self::STATUS_FAILED => [
            self::STATUS_FAILED,
            self::STATUS_NEW,
            self::STATUS_GENERATED,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'field',
        'image_path',
        'locale',
        'image_hash',
        'page_url',
        'current_alt',
        'current_title',
        'suggested_alt',
        'suggested_title',
        'approved_alt',
        'approved_title',
        'status',
        'prompt_context',
        'model',
        'tokens_used',
        'error',
        'generated_at',
        'reviewed_at',
        'applied_at',
        'reviewed_by',
    ];

    /**
     * The attributes that should be cast.
     *
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
     * Set a status while enforcing the alt-suggestion workflow.
     *
     * @param string $to
     * @return $this
     */
    public function setStatus(string $to): self
    {
        if (!in_array($to, self::STATUSES, true)) {
            throw new InvalidArgumentException('Unknown image alt suggestion status: ' . $to);
        }

        $from = $this->status ?: null;

        if ($from === null || $from === $to) {
            $this->setAttribute('status', $to);

            return $this;
        }

        if (!in_array($from, self::STATUSES, true)) {
            throw new InvalidArgumentException('Unknown current image alt suggestion status: ' . $from);
        }

        $allowed = self::TRANSITIONS[$from] ?? [];

        if (!in_array($to, $allowed, true)) {
            throw new InvalidArgumentException('Invalid image alt suggestion status transition: ' . $from . ' -> ' . $to);
        }

        $this->setAttribute('status', $to);

        return $this;
    }

    /**
     * Get the model that owns the image.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the Voyager user who reviewed the suggestion.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applyLogs(): HasMany
    {
        return $this->hasMany(ImageAltApplyLog::class, 'suggestion_id');
    }
}
