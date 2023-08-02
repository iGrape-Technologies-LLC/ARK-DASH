<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Feature extends Model
{
    
    public function values() {
    	return $this->hasMany('App\Models\FeatureValue');
    }
}
