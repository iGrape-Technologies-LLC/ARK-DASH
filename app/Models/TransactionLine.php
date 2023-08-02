<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;

class TransactionLine extends Model
{
    protected $fillable = ['article_property_id', 'transaction_id', 'price', 'quantity', 'discount_id', 'discount_amount'];

    public function article_property() {
    	return $this->belongsTo('App\Models\ArticleProperty');
    }

    public function priceFormatted() {
        return UtilGeneral::number_format($this->price);
    }

    public function getFinalPriceAttribute() {
        $price = 0;
        if(!is_null($this->price) && $this->price > 0) {
            $price = $this->price;
        }

        if(!is_null($this->discount_amount) && $this->discount_amount > 0) {
            $price -= $this->discount_amount;
        }

        return $price;
    }

    public function getFinalPriceFormattedAttribute() {
        return UtilGeneral::number_format($this->final_price);
    }

    public function totalFormatted(){
    	return UtilGeneral::number_format($this->final_price * $this->quantity);
    }

}
