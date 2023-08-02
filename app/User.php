<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\PageContact;
use App\Notifications\ArticleContact;
use App\Notifications\ContactFormNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use LogsActivity;

    protected static $logAttributes = ['name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'lastname', 'email', 'password', 'photo', 'phone', 'address', 'postal_code', 'approved_at', 'doc_number', 'alternative_email', 'token', 'refreshToken'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function fullName(){
        
        if(!empty($this->lastname)) return $this->name . ' ' . $this->lastname;

        return $this->name;


    }

    public function whatsapp(){
        if(!empty($this->phone)) {
            return "https://api.whatsapp.com/send?phone=".$this->phone;
        }
        return null;
    }

    public function articles() {
        return $this->hasMany('App\Models\Article');
    }

    public function roles() {
        return $this->belongsToMany('Spatie\Permission\Models\Role', 'model_has_roles', 'model_id');
    }

    public function favorites() {
        return $this->hasMany('App\Models\UserFavorites');
    }

    public function city() {
        return $this->belongsTo('App\Models\City');
    }

    public function addresses() {
        return $this->hasMany('App\Models\Address');
    }

    public function isAdmin() {
        foreach($this->roles as $rol) {
            if($rol->name == 'Admin' || $rol->name == 'SuperAdmin') {
                return true;
            }
        }

        return false;
    }

    public function isStaff(){
        foreach($this->roles as $rol) {
            if($rol->is_staff) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(){

        if(!empty(auth()->user()->roles)){
            foreach (auth()->user()->roles as $role) {
                if($role->is_default == 1){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token, $this->email));
    }

    public function sendEmailVerificationNotification() {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPageContactNotification($nombre, $email, $asunto, $mensaje) {
        $this->notify(new PageContact($nombre, $email, $asunto, $mensaje));
    }

    public function sendContactFormNotification($data) {
        $this->notify(new ContactFormNotification($data));
    }

    public function sendArticleContactNotification($id, $name, $email, $message) {
        $this->notify(new ArticleContact($id, $name, $email, $message));
    }

    
}
