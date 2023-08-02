<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Advertisement extends Model
{
	use SoftDeletes;

	use LogsActivity;

    protected static $logAttributes = ['path'];
	
    protected $fillable = ['path', 'link_to', 'home', 'aside', 'inter_product', 'active'];
}
