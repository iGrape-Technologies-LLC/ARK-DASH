<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Video extends Model
{
	use LogsActivity;

    protected static $logAttributes = ['name'];
    protected $fillable = ['path', 'name'];

    public function article() {
    	return $this->belongsTo('App\Models\Article');
    }
}
