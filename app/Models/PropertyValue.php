<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PropertyValue extends Model
{
    protected $fillable = ['possible_value'];

    public function articleproperty() {
        return $this->belongsToMany('App\Models\ArticleProperty', 'combinations')
                    ->using('App\Models\Combination');
    }

    public function property() {
    	return $this->belongsTo('App\Models\Property');
    }

    private static function findPropertyValues($property_name) {
        return self::whereHas('property', function($query) use ($property_name) {
            $query->where('name', $property_name);
        })->get();
    }

    public static function getBrands() {
    	return self::findPropertyValues('Marca')->sortBy('possible_value');
    }

    public static function getConditions() {
    	return self::findPropertyValues('Condicion');
    }

    public static function getTransmissions() {
        return self::findPropertyValues('Transmision')->sortBy('possible_value');
    }

    public static function getFuelTypes() {
        return self::findPropertyValues('Combustible');
    }
}
