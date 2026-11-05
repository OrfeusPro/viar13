<?php

namespace TCG\Voyager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $guarded = [];

    public function link($absolute = false)
    {
        $parameters = $this->parameters;

        if (is_string($parameters)) {
            $parameters = json_decode($parameters, true);
        } elseif (is_object($parameters)) {
            $parameters = json_decode(json_encode($parameters), true);
        }

        $parameters = is_array($parameters) ? $parameters : [];

        if ($this->route !== null && $this->route !== '') {
            return Route::has($this->route)
                ? route($this->route, $parameters, $absolute)
                : '#';
        }

        $path = (string) ($this->url ?? '');

        return $absolute ? url($path) : $path;
    }
}
