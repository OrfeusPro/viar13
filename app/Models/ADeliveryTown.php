<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;
use TCG\Voyager\Models\Translation;

class ADeliveryTown extends Model
{
    use Translatable;
    protected $table = 'a_delivery_towns';
    protected $translatable = ['city'];

    public function translations()
    {
        return $this->hasMany(Translation::class, 'foreign_key', 'id')
            ->where('table_name', 'a_delivery_towns');
    }
}
