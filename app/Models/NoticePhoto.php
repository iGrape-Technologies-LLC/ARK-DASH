<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class NoticePhoto extends Model
{
	use LogsActivity;

    protected static $logAttributes = ['name'];
    
    protected $fillable = ['path', 'principal', 'name'];

    public function notice() {
    	return $this->belongsTo('App\Models\Notice');
    }
}
