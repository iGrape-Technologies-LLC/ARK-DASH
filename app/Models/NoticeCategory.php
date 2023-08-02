<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class NoticeCategory extends Model
{

    use LogsActivity;

    protected static $logAttributes = ['name'];

     /**
     * The attributes that are mass assignable.
     *  
     * @var array
     */
    protected $fillable = ['name'];

    public function notices()
    {
        return $this->belongsToMany('App\Notice');
    }

    public static function forDropdown($show_none = false)
    {
        $categories = NoticeCategory::pluck('name', 'id');

        if ($show_none) {
            $categories->prepend(__('custom.none'), '');
        }

        return $categories;
    }


}
