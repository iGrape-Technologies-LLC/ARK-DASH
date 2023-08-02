<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Whatsapp extends Model
{
	use SoftDeletes;
    use LogsActivity;

    protected static $logAttributes = ['name', 'phone', 'hour_from', 'hour_to'];

    protected $fillable = ['name', 'phone', 'hour_from', 'hour_to'];

    public function getHourFromAttribute($value) 
    { 
        return date('H:i', strtotime($value));
    } 
     
    public function getHourToAttribute($value)
    { 
        return date('H:i', strtotime($value));
    }
}
