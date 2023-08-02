<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserFavorites extends Model
{
	protected $fillable = ['user_id', 'article_id'];

    public function user() {
    	return $this->belongsTo('App\User');
    }

    public function article() {
    	return $this->belongsTo('App\Models\Article');
    }
}
