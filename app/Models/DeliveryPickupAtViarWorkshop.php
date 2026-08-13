<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class DeliveryPickupAtViarWorkshop extends Model
{
    use Translatable;
    protected $table = 'delivery_pickup_at_viar_workshop';
    protected $translatable = ['title'];
}
