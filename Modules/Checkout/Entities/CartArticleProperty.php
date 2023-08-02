<?php

namespace Modules\Checkout\Entities;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Utils\UtilGeneral;

class CartArticleProperty extends Pivot
{

		public function article_property() {
			return $this->belongsTo('App\Models\ArticleProperty');
		}

  	public function getLineTotalAttribute() {
  		if($this->article_property != null && $this->article_property->price != null && $this->quantity != null) {
        $price = $this->article_property->price;

        $current_discount = $this->article_property->article->current_discount;
        if($current_discount != null) {
          if($current_discount->type == 'fixed') {
            $price -= $current_discount->amount;
          } elseif($current_discount->type == 'percentage') {
            $price -= $price * $current_discount->amount / 100;
          }
        }

  			return ($price * $this->quantity);
  		} else {
  			return 0;
  		}
  	}

    public function getLineTotalFormattedAttribute() {
      return UtilGeneral::number_format($this->line_total);
    }
}

?>
