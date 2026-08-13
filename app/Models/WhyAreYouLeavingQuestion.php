<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;


class WhyAreYouLeavingQuestion extends Model
{
    use Translatable;

    protected $table = 'why_are_you_leaving_questions';
    protected $translatable = ['title'];
}
