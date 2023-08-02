<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class City extends Model
{
	use LogsActivity;

    protected static $logAttributes = ['name'];
    
    protected $fillable = ['name', 'state_id'];

    public function state() {
    	return $this->belongsTo('App\Models\State');
    }

    public function articles() {
    	return $this->hasMany('App\Models\Article');
    }
}
