<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Utils\UtilGeneral;

class Notice extends Model
{
    use LogsActivity;
    use Sluggable;

    protected static $logAttributes = ['title'];
     /**
     * The attributes that are mass assignable.
     *  
     * @var array
     */
    protected $fillable = ['title', 'short_description', 'description', 'author', 'publish_date', 'active', 'created_by'];

    public function photos() {
    	return $this->hasMany('App\Models\NoticePhoto');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\NoticeCategory');
    }

    public function categoriesResumeName(){
        return implode(', ', $this->categories()->pluck('name')->toArray());
    }

    public function getTitleForURL() {        
        return urlencode(str_replace(' ', '-', $this->title));
    }

    public function date(){
        return UtilGeneral::format_date_short($this->publish_date, false);
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
                'source' => 'title'
            ]
        ];
    }
    
    public function getPhotosJson() {
        $photos = [];

        foreach($this->photos as $photo) {
            $photoJson = [];
            $photoJson['filename'] = $photo->path;
            $photoJson['originalName'] = $photo->name;

            $photos[] = $photoJson;
        }

        return json_encode($photos);
    }

    public function getPrincipalPhoto() {

        if(count($this->photos)>0){
            foreach($this->photos as $photo) { 
                if($photo->principal) {
                    $photo->path = 'storage/'.$photo->path;
                    return $photo;
                }
            }
            $this->photos[0]->path = 'storage/'.$this->photos[0]->path;
            return $this->photos[0];
        } else {
            $photoDefault = $this->getPhotoDefault();

            return $photoDefault;
        }                   
    }

    public function getPhotoDefault(){
        return new Photo([
                'path' => '_business/default.jpg',
                'principal' => 1,
                'name' => 'Default'
            ]);
    }
}
