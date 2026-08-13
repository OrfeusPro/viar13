<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit row for one alt/title write performed by alt:apply.
 *
 * @property int $id
 * @property int $suggestion_id
 * @property string|null $imageable_type
 * @property int|null $imageable_id
 * @property string|null $field
 * @property string|null $locale
 * @property string|null $source_type
 * @property int|null $media_id
 * @property string $column_written
 * @property string|null $old_value
 * @property string|null $new_value
 * @property int|null $applied_by
 * @property \Illuminate\Support\Carbon|null $applied_at
 * @property string $source
 */
class ImageAltApplyLog extends Model
{
    /**
     * @var string
     */
    protected $table = 'image_alt_apply_log';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'suggestion_id',
        'imageable_type',
        'imageable_id',
        'field',
        'locale',
        'source_type',
        'media_id',
        'column_written',
        'old_value',
        'new_value',
        'applied_by',
        'applied_at',
        'source',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(ImageAltSuggestion::class, 'suggestion_id');
    }
}
