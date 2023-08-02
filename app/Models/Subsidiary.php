<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subsidiary extends Model
{
    protected $fillable = ['name', 'city_id', 'street', 'street_number', 'postal_code', 'floor', 'apartment'];

    public function city() {
    	return $this->belongsTo('App\Models\City');
    }

    public function getFullAddressAttribute() {
    	$apartment = '';

    	if($this->floor != null && $this->apartment != null) {
    		$apartment .= ' ' . $this->floor . ' ' . $this->apartment;
    	}

    	return $this->street . ' ' . $this->street_number . $apartment;
    }
}
