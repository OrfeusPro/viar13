<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;
class APainterImagesStatus extends Model
{
    use Translatable;
    protected $table = 'a_painter_images_status';
	protected $translatable = ['title'];
    
	public static function getPainterImagesStatus($id)
    {
        try {
            $status = APainterImagesStatus::where('id', $id)->first();
        } catch (Throwable $th) {
            $status = null;
        }

        return $status;
    }

}
