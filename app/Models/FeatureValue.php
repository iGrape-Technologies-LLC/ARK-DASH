<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureValue extends Model
{
    
    public function feature() {
    	return $this->belongsTo('App\Models\Feature');
    }

    public function articles() {
    	return $this->belongsToMany('App\Models\Article');
    }
}
