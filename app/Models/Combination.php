<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class Combination extends Pivot
{
    
    public function propertyvalue() {
    	return $this->hasOne('App\Models\PropertyValue');
    }

    public function articleproperty() {
    	return $this->hasOne('App\Models\ArticleProperty');
    }
}
