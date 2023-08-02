<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Cviebrock\EloquentSluggable\Sluggable;

class Category extends Model
{
    use SoftDeletes;
    use LogsActivity;
    use Sluggable;

    protected static $logAttributes = ['name'];

    protected $fillable = ['name', 'parent_id', 'priority','photo'];

    public function articles() {
        return $this->hasMany('App\Models\Article');
    }

    public function parent() {
        return $this->belongsTo('App\Models\Category');
    }

    public function subcategories() {
        return $this->hasMany('App\Models\Category', 'parent_id');
    }

    public function has_subcategory($breadcrumbs) {
        $last_slug = null;
        if(!is_null($breadcrumbs)) {
            $slugsarr = explode('/', $breadcrumbs);

            if(count($slugsarr) > 0) {
                $last_slug = $slugsarr[count($slugsarr)-1];
            }
        }

        foreach($this->subcategories as $subcategory) {
            if($subcategory->slug == $last_slug) {
                return true;
            } else{
                if($subcategory->has_subcategory($breadcrumbs)){
                    return true;
                }
            }
        }

        return false;
    }

    public static function forDropdown()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        $dropdown =  $categories->pluck('name', 'id');

        return $dropdown;
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
}
