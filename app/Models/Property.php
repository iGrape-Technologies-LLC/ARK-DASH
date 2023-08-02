<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

/*
*
* IMPORTANT NOTE:
* This class was called Attribute before
* thats why in some files of the project can be seen 
* variables called "attribute" instead of "property"
*
*/

class Property extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected static $logAttributes = ['name'];
    
    protected $fillable = ['name', 'order'];

    public function articles() {
    	return $this->belongsToMany('App\Models\Article')
    				->using('App\Models\ArticleProperty')
                    ->withPivot('value', 'propertyvalue_id');
    }

    public function values() {
    	return $this->hasMany('App\Models\PropertyValue')->orderBy('order');
    }

    public function hasPosiblesValues($article){

        foreach ($this->values as $value) {
            $exists = true;
            if(!$article->hasValuePosible($value->id)){                
                $exists = false;
            }      
            if($exists || (!$exists && config('config.SHOW_ATRIBUTES_WITHOUT_ARTICLES'))) return true;
        }

        return false;

    }
}
