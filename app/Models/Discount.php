<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Discount extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = ['name', 'active', 'date_from', 'date_until', 'amount', 'type'];

    public function articles()
    {
        return $this->belongsToMany('App\Models\Article', 'discount_article');
    }    

    public function getDateFromFormattedAttribute(){
        $format = 'j \de F';

        return  Date::createFromTimestamp(strtotime($this->date_from))->format($format);
    }

    public function getDateUntilFormattedAttribute(){
        $format = 'j \de F';

        return  Date::createFromTimestamp(strtotime($this->date_until))->format($format);
    }

    public function getArticlesJson() {
        $photos = [];

        foreach($this->articles as $category) {
            $photos[] = $category->id;
        }

        return json_encode($photos);
    }
}
