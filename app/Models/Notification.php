<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;

class Notification extends Model
{
    protected $fillable = ['name', 'email', 'subject', 'message', 'read', 'user_id'];

    public function date(){
        return UtilGeneral::format_date_short($this->created_at, true);
    }
}
