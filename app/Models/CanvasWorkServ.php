<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\CanvasWorkServ
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property string|null $all_our_work_title
 * @property string|null $why_viar
 * @property string|null $we_love_clients
 * @property string|null $why1_title
 * @property string|null $why2_title
 * @property string|null $why3_title
 * @property string|null $why4_title
 * @property string|null $why5_title
 * @property string|null $why6_title
 * @property string|null $why7_title
 * @property string|null $why8_title
 * @property string|null $imp_know_title
 * @property string|null $imp_know_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read null $translated
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|CanvasWorkServ newModelQuery()
 * @method static Builder|CanvasWorkServ newQuery()
 * @method static Builder|CanvasWorkServ query()
 * @method static Builder|CanvasWorkServ whereAllOurWorkTitle($value)
 * @method static Builder|CanvasWorkServ whereCreatedAt($value)
 * @method static Builder|CanvasWorkServ whereId($value)
 * @method static Builder|CanvasWorkServ whereImpKnowText($value)
 * @method static Builder|CanvasWorkServ whereImpKnowTitle($value)
 * @method static Builder|CanvasWorkServ whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|CanvasWorkServ whereUpdatedAt($value)
 * @method static Builder|CanvasWorkServ whereWeLoveClients($value)
 * @method static Builder|CanvasWorkServ whereWhy1Title($value)
 * @method static Builder|CanvasWorkServ whereWhy2Title($value)
 * @method static Builder|CanvasWorkServ whereWhy3Title($value)
 * @method static Builder|CanvasWorkServ whereWhy4Title($value)
 * @method static Builder|CanvasWorkServ whereWhy5Title($value)
 * @method static Builder|CanvasWorkServ whereWhy6Title($value)
 * @method static Builder|CanvasWorkServ whereWhy7Title($value)
 * @method static Builder|CanvasWorkServ whereWhy8Title($value)
 * @method static Builder|CanvasWorkServ whereWhyViar($value)
 * @method static Builder|CanvasWorkServ withTranslation($locale = null, $fallback = true)
 * @method static Builder|CanvasWorkServ withTranslations($locales = null, $fallback = true)
 */
class CanvasWorkServ extends Model
{
    use Translatable;

    protected $table = 'canvas_work_serv';

    protected $fillable = [];

    protected $translatable = [
        'all_our_work_title', 'why_viar', 'we_love_clients', 'why1_title', 'why2_title', 'why3_title', 'why4_title',
        'why5_title', 'why6_title', 'why7_title', 'why8_title',

        'imp_know_title', 'imp_know_text',
    ];
}
