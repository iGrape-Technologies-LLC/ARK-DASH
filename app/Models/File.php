<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['original_name', 'path'];

    public function articles() {
    	return $this->belongsToMany('App\Models\Article');
    }
}
