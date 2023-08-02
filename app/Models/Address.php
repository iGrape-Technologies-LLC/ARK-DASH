<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['name', 'city_id', 'street', 'street_number', 'postal_code', 'user_id', 'city'];

    public function city() {
    	return $this->belongsTo('App\Models\City');
    }

    public function user() {
    	return $this->belongsTo('App\User');
    }
}
