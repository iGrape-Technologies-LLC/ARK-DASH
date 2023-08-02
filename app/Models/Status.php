<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Status extends Model
{
	use LogsActivity;

    protected static $logAttributes = ['name'];
    protected $fillable = ['name', 'color', 'priority'];

    public static function forDropdown()
    {
        $categories = Status::orderBy('name', 'asc')->get();
        $dropdown =  $categories->pluck('name', 'id');

        return $dropdown;
    }
    
}
