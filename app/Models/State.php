<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class State extends Model
{
	use LogsActivity;

    protected static $logAttributes = ['name'];
    protected $fillable = ['name'];

    public function cities() {
    	return $this->hasMany('App\Models\City');
    }

    public function country() {
    	return $this->belongsTo('App\Models\Country');
    }
}
