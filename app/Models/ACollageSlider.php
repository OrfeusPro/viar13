<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Resizable;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\NewhomeTopSlider
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $image_mob
 * @method static Builder|NewhomeTopSlider newModelQuery()
 * @method static Builder|NewhomeTopSlider newQuery()
 * @method static Builder|NewhomeTopSlider query()
 * @method static Builder|NewhomeTopSlider whereCreatedAt($value)
 * @method static Builder|NewhomeTopSlider whereId($value)
 * @method static Builder|NewhomeTopSlider whereImage($value)
 * @method static Builder|NewhomeTopSlider whereImageMob($value)
 * @method static Builder|NewhomeTopSlider whereUpdatedAt($value)
 */
class ACollageSlider extends Model
{
    use Resizable;
    use Translatable;

    protected $table = 'a_collage_slider';
	protected $translatable = ['size_text', 'size_title'];
}
