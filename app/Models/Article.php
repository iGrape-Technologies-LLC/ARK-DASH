<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Utils\UtilGeneral;

class Article extends Model
{
    use SoftDeletes;
    use Sluggable;
    use LogsActivity;

    protected static $logAttributes = ['title'];
    
    protected $fillable = ['title', 'description', 'price', 'type', 'enable_stock', 'active', 'featured', 'user_id', 'currency_id', 'sku', 'visits_count', 'priority', 'weight', 'size_x', 'size_y', 'size_z'];

    public function properties() {
    	return $this->hasMany('App\Models\ArticleProperty');
    }

    public function features() {
        return $this->belongsToMany('App\Models\FeatureValue');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category');
    }    

    public function brands()
    {
        return $this->belongsToMany('App\Models\Brand', 'brand_article');
    }

     public function brandsResumeName(){
        return implode(', ', $this->brands()->pluck('name')->toArray());
    }

    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag', 'tag_article');
    }

    public function categoriesResumeName(){
        return implode(', ', $this->categories()->pluck('name')->toArray());
    }

    public function tagsResumeName(){
        return implode(', ', $this->tags()->pluck('name')->toArray());
    }

    public function photos() {
    	return $this->hasMany('App\Models\Photo');
    }

    public function videos() {
        return $this->hasMany('App\Models\Video');
    }

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function city() {
        return $this->belongsTo('App\Models\City');
    }

    public function favorites() {
        return $this->hasMany('App\Models\UserFavorites');
    }

    public function discounts() {
        return $this->belongsToMany('App\Models\Discount', 'discount_article');
    }

    public function currency() {
        return $this->belongsTo('App\Models\Currency');
    }

    public function files() {
        return $this->belongsToMany('App\Models\File');
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

    public function categoriesName(){
        return $this->categories()->pluck('name');
    }

    public function outOfStock(){
        if(config('config.MANAGE_STOCK')){
            $stock = 0;            
            foreach ($this->properties as $article_property) {
                if($article_property->stock != null) {
                    $stock += $article_property->stock;
                }
            }
            if($stock<=0) return true;
        }        
        return false;        
    }

    public function getIsUserFavoriteAttribute() {
        $user = auth()->user();

        if($user != null) {
            foreach ($this->favorites as $favorite) {
                if($favorite->user_id == $user->id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function stockTotal(){
        if(config('config.MANAGE_STOCK')){
            $stock = 0;
            foreach ($this->properties as $article_property) {
                $stock += $article_property->stock;
            }
            return $stock;
        }

        return 0;        
    }

    public function hasTransactions() {
        $has_transactions = false;

        foreach($this->properties as $art_property) {
            $cant_transactions = $art_property->transaction_lines()->count();
            
            if($cant_transactions >  0) {
                $has_transactions = true;
                break;
            }
        }

        return $has_transactions;
    }

    public function getCategoriesJson() {
        $photos = [];

        foreach($this->categories as $category) {
            $photos[] = $category->id;
        }

        return json_encode($photos);
    }

    public function getCondition() {
        foreach($this->properties as $property) {
            if($property->name == 'Condicion') {
                $cond = PropertyValue::find($property->pivot->propertyvalue_id);
                
                if($cond != null) {
                    return $cond->possible_value;
                }
            }
        }
    }

    public function getCurrentDiscountAttribute() {
        foreach($this->discounts as $discount) {
            if($discount->active) {
                $date_from = \DateTime::createFromFormat('Y-m-d H:i:s', $discount->date_from . ' 00:00:00');
                $date_until = \DateTime::createFromFormat('Y-m-d H:i:s', $discount->date_until . ' 23:59:59');
                $today = new \DateTime('now');

                if($today >= $date_from && $today <= $date_until) {
                    return $discount;
                }
            }
        }

        return null;
    }

    public function hasValuePosible($valueId){
        foreach ($this->properties as $article_property) {
            foreach ($article_property->values as $value) {
                if($value->id == $valueId) return true;
            }
        }
        return false;
    }

    public function hasInFirstAtribute($valueId){
        $article_property = $this->properties[0];
        foreach ($article_property->values as $value) {
            if($value->id == $valueId) return true;
        }
        
        return false;
    }

    public function getPriceFormatted() {
        if($this->price != null) {
            return UtilGeneral::number_format($this->price, $this->currency->symbol);
        } else {
            return null;
        }
    }

    public function getFullPriceWithoutDiscountFormatted() {
        $minPrice = null;
        $maxPrice = null;
        foreach ($this->properties as $property) {
            if($minPrice == null) $minPrice = $property->price;
            if($maxPrice == null) $maxPrice = $property->price;
            if($property->price>$maxPrice) $maxPrice = $property->price;
            if($property->price<$minPrice) $minPrice = $property->price;
        }

        return ($minPrice == $maxPrice) ? UtilGeneral::number_format($minPrice, $this->currency->symbol) : UtilGeneral::number_format($minPrice, $this->currency->symbol) . ' - ' . UtilGeneral::number_format($maxPrice, $this->currency->symbol);
    }

    public function getFullPriceFormatted() {
        //retorna el precio formateado con formato $precio1 - $precio2 en caso de que
        //haya atributos con diferentes precios
        $minPrice = null;
        $maxPrice = null;
        foreach ($this->properties as $property) {
            if($minPrice == null) $minPrice = $property->price;
            if($maxPrice == null) $maxPrice = $property->price;
            if($property->price>$maxPrice) $maxPrice = $property->price;
            if($property->price<$minPrice) $minPrice = $property->price;
        }

        if($this->current_discount != null) {
            if($this->current_discount->type == 'fixed') {
                $minPrice -= $this->current_discount->amount;
                $maxPrice -= $this->current_discount->amount;
            } elseif($this->current_discount->type == 'percentage') {
                $minPrice -= $minPrice * $this->current_discount->amount / 100;
                $maxPrice -= $maxPrice * $this->current_discount->amount / 100;
            }
        }

        return ($minPrice == $maxPrice) ? UtilGeneral::number_format($minPrice, $this->currency->symbol) : UtilGeneral::number_format($minPrice, $this->currency->symbol) . ' - ' . UtilGeneral::number_format($maxPrice, $this->currency->symbol);
       
    }

    public static function getFeatured() {
        $articles = self::with('properties')
                                ->where('active', true)
                                ->where('featured', true)
                                ->orderBy('priority', 'ASC');

        if(config('config.SHOW_PRICING')){
            $articles = $articles->whereHas('properties', function($properties){
                                $properties->where('price', '>', '0');
                        });
            
        } 

        if(config('config.SHIPPING_REQUIRED')){
            $articles = $articles->whereNotNull('weight')->whereNotNull('size_x')->whereNotNull('size_y')->whereNotNull('size_z');
        }

        return $articles->get();
    }

    public function isValidToShow(){        

        if(config('config.SHOW_PRICING')){
            foreach ($this->properties as $property) {
                if(empty($property->price) || $property->price < 0) return false;
            }
        }
        
        if(config('config.SHIPPING_REQUIRED')){
            if(empty($this->weight) || empty($this->size_x) || empty($this->size_y) || empty($this->size_z)) return false;
        }

        return true;
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

    public function getFilesJson() {
        $files = [];

        foreach($this->files as $file) {
            $fileJson = [];
            $fileJson['filename'] = $file->path;
            $fileJson['originalName'] = $file->original_name;

            $files[] = $fileJson;
        }

        return json_encode($files);
    }

    public function thumb(){
       if(count($this->photos)>0){
            foreach($this->photos as $photo) { 
                if($photo->principal) {
                    //$photo->path = 'storage/thumb_'.$photo->path;
                    return 'storage/thumb_'.$photo->path;
                }
            }
            //$this->photos[0]->path = 'storage/thumb_'.$this->photos[0]->path;
            return 'storage/thumb_'.$this->photos[0]->path;
        } else {
            $photoDefault = $this->getPhotoDefault();

            return $photoDefault->path;
        }     
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

    public static function getMostVisiteds() {
        return self::orderBy('visits_count', 'desc')
                ->where('active', true)
                ->limit(4)
                ->get();
    }

    public function loaderPath(){
        return asset('transversal/img/blank.gif' );
    }
}
