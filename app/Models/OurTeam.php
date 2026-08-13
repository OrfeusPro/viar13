<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class OurTeam extends Model
{
	use Translatable;
    protected $table = 'our_team';
	protected $translatable = ['title', 'job_title', 'text', 'short_text'];
}
