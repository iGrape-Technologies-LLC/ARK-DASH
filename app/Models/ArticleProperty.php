<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;


class ArticleProperty extends Model {

	protected $fillable = ['article_id', 'price', 'stock', 'stock_limit'];

	public function article() {
		return $this->belongsTo('App\Models\Article');
	}

	public function values() {
		return $this->belongsToMany('App\Models\PropertyValue', 'combinations')
								->using('App\Models\Combination');
	}

	public function valuesResumeName(){
		return implode(', ', $this->values()->pluck('possible_value')->toArray());
	}


	  public function transaction_lines() {
	      return $this->hasMany('App\Models\TransactionLine');
	  }

	public function getDiscountAttribute() {
		if(!is_null($this->price)) {
			return ($this->price - $this->final_price);
		} else {
			return 0;
		}
	}

	public function getFinalPriceAttribute() {
		if($this->price != null) {
			$price = $this->price;

			$current_discount = $this->article->current_discount;
			if($current_discount != null) {
				if($current_discount->type == 'fixed') {
          $price -= $current_discount->amount;
        } elseif($current_discount->type == 'percentage') {
          $price -= $price * $current_discount->amount / 100;
        }
			}

			return $price;
		} else {
			return 0;
		}
	}

	public function getPriceFormatted() {
		return UtilGeneral::number_format($this->final_price,  $this->article->currency->symbol);
	}	

	public function articleHasStock($quantity = 1){
		if (config('config.MANAGE_STOCK')){
			return $this->stock >= $quantity ? true : false;
		} else{
			return true;
		}
	}

	 public function underLimitStock(){
        if(config('config.MANAGE_STOCK')){       
        	if(!empty($this->stock_limit)){
	            if($this->stock<=$this->stock_limit){
	            	return true;
	            }
	        }
        }        
        return false;        
    }
}
