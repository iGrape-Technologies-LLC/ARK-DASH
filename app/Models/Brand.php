<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Brand extends Model
{
	use LogsActivity;

	protected static $logAttributes = ['name'];
	
    protected $fillable = ['name'];

    public function articles() {
    	return $this->hasMany('App\Models\Article', 'brand_article');
    }
}
