<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;

/**
 * App\Models\MenuItem
 *
 * @mixin Eloquent
 * @mixin Builder
 * @property int $id
 * @property int|null $menu_id
 * @property string $title
 * @property string $url
 * @property string $target
 * @property string|null $icon_class
 * @property string|null $color
 * @property int|null $parent_id
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $route
 * @property string|null $parameters
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\MenuItem[] $children
 * @property-read int|null $children_count
 * @property-read null $translated
 * @property-read \TCG\Voyager\Models\Menu|null $menu
 * @property-read \Illuminate\Database\Eloquent\Collection|\TCG\Voyager\Models\Translation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|MenuItem newModelQuery()
 * @method static Builder|MenuItem newQuery()
 * @method static Builder|MenuItem query()
 * @method static Builder|MenuItem whereColor($value)
 * @method static Builder|MenuItem whereCreatedAt($value)
 * @method static Builder|MenuItem whereIconClass($value)
 * @method static Builder|MenuItem whereId($value)
 * @method static Builder|MenuItem whereMenuId($value)
 * @method static Builder|MenuItem whereOrder($value)
 * @method static Builder|MenuItem whereParameters($value)
 * @method static Builder|MenuItem whereParentId($value)
 * @method static Builder|MenuItem whereRoute($value)
 * @method static Builder|MenuItem whereTarget($value)
 * @method static Builder|MenuItem whereTitle($value)
 * @method static Builder|MenuItem whereTranslation(string $field, string $operator, string $value = null, string $locales = null, string $default = true)
 * @method static Builder|MenuItem whereUpdatedAt($value)
 * @method static Builder|MenuItem whereUrl($value)
 * @method static Builder|MenuItem withTranslation($locale = null, $fallback = true)
 * @method static Builder|MenuItem withTranslations($locales = null, $fallback = true)
 */
class MenuItem extends \TCG\Voyager\Models\MenuItem
{
    use Translatable;

    protected $translatorMethods = [

        'link' => 'translatorLink',

    ];

    protected $table = 'menu_items';

    protected $guarded = [];

    protected $translatable = ['title', 'url'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->menu->removeMenuFromCache();
        });

        static::saved(function ($model) {
            $model->menu->removeMenuFromCache();
        });

        static::deleted(function ($model) {
            $model->menu->removeMenuFromCache();
        });
    }

    public function children()
    {
        return $this->hasMany(Voyager::modelClass('MenuItem'), 'parent_id')
            ->with('children')
            ->orderBy('order');
    }

    public function menu()
    {
        return $this->belongsTo(Voyager::modelClass('Menu'));
    }

    public function link($absolute = false)
    {
        return $this->prepareLink($absolute, $this->route, $this->parameters, $this->url);
    }

    protected function prepareLink($absolute, $route, $parameters, $url)
    {
        if (is_null($parameters)) {
            $parameters = [];
        }

        if (is_string($parameters)) {
            $parameters = json_decode($parameters, true);
        } elseif (is_array($parameters)) {
            $parameters = $parameters;
        } elseif (is_object($parameters)) {
            $parameters = json_decode(json_encode($parameters), true);
        }

        if (!is_null($route)) {
            if (!Route::has($route)) {
                return '#';
            }

            return route($route, $parameters, $absolute);
        }

        if ($absolute) {
            return url($url);
        }

        return $url;
    }

    public function translatorLink($translator, $absolute = false)
    {
        return $this->prepareLink($absolute, $translator->route, $translator->parameters, $translator->url);
    }

    public function getParametersAttribute()
    {
        return json_decode($this->attributes['parameters']);
    }

    public function setParametersAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['parameters'] = $value;
    }

    public function setUrlAttribute($value)
    {
        if (is_null($value)) {
            $value = '';
        }

        $this->attributes['url'] = $value;
    }

    /**
     * Return the Highest Order Menu Item.
     *
     * @param  number  $parent  (Optional) Parent id. Default null
     *
     * @return number Order number
     */
    public function highestOrderMenuItem($parent = null)
    {
        $order = 1;

        $item = $this->where('parent_id', '=', $parent)
            ->orderBy('order', 'DESC')
            ->first();

        if (!is_null($item)) {
            $order = intval($item->order) + 1;
        }

        return $order;
    }
}
