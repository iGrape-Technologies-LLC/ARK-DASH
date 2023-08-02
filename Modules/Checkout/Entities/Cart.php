<?php

namespace Modules\Checkout\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function transaction() {
    	return $this->hasOne('App\Models\Transaction');
    }

    public function article_properties() {
    	return $this->belongsToMany('App\Models\ArticleProperty', 'cart_article_property')
            ->using(CartArticleProperty::class)
            ->withPivot('quantity');
    }

    public function userCurrentCart($user) {
        if($user != null) {
            $user_id = $user->id;
            $session_id = null;
        } else {
            $user_id = null;
            $session_id = session()->getId();
        }

    	return $this->where('user_id', $user_id)
            ->where('session_id', $session_id)
            ->where(function($query) {
                // debe tener una transaccion que no este aprobada o no tener transaccion
                $query->whereHas('transaction', function($transaction) {
                    $transaction->where('status', 'pending');
                })->orWhereDoesntHave('transaction');
            })
            ->orderBy('created_at', 'desc')
            ->with('article_properties')
            ->first();
    }

    /* 
    * Valida que los articulos del carro tengan stock
    * y que esten activos. En caso contrario los quita
    * del carro
    */
    public function syncArticlesByStockAndAvailability() {
        $changed = false;

        foreach($this->article_properties as $article_property) {
            if($article_property != null) {
                if($article_property->article != null && $article_property->article->active) {
                    if(config('config.MANAGE_STOCK') == 'true') {
                        if(!is_null($article_property->stock) && $article_property->stock < $article_property->pivot->quantity) {
                            $this->article_properties()->detach($article_property->id);
                            $changed = true;
                        }
                    }
                } else {
                    $this->article_properties()->detach($article_property->id);
                    $changed = true;
                }
            }
        }

        return $changed;
    }

    public function getDiscountAttribute() {
        $discount = 0;

        foreach($this->article_properties as $article_property) {
            if(!is_null($article_property->price) && !is_null($article_property->pivot->quantity)) {
                $discount += ($article_property->discount * $article_property->pivot->quantity);
            }
        }

        return $discount;
    }

    public function getSubtotalAttribute() {
        $total = 0;

        foreach($this->article_properties as $article_property) {
            if($article_property->price != null && $article_property->pivot != null && $article_property->pivot->quantity != null) {
                $total += $article_property->price * $article_property->pivot->quantity;
            }
        }

        return $total;
    }

    public function getTotalAttribute() {
    	$total = 0;

    	foreach($this->article_properties as $article_property) {
            if($article_property->price != null && $article_property->pivot != null && $article_property->pivot->quantity != null) {
                $total += $article_property->final_price * $article_property->pivot->quantity;
            }
    	}

    	return $total;
    }

    public function getQuantityTotalAttribute() {
        $quantity = 0;

        foreach($this->article_properties as $article_property) {
            $quantity += $article_property->pivot->quantity;
        }

        return $quantity;
    }

    public function getDiscountFormattedAttribute() {
        $symbol = '$';
        if(count($this->article_properties) > 0) {
            $symbol = $this->article_properties[0]->article->currency->symbol;
        }

        return UtilGeneral::number_format($this->discount, $symbol);
    }

    public function getSubtotalFormattedAttribute() {
        $symbol = '$';
        if(count($this->article_properties) > 0) {
            $symbol = $this->article_properties[0]->article->currency->symbol;
        }

        return UtilGeneral::number_format($this->subtotal, $symbol);
    }

    public function getTotalFormattedAttribute() {
        $symbol = '$';
        if(count($this->article_properties) > 0) {
            $symbol = $this->article_properties[0]->article->currency->symbol;
        }

        return UtilGeneral::number_format($this->total, $symbol);
    }

    public function calculatePackageDimensions($isKg) {
        $dimensions_width = 0;
        $dimensions_height = 0;
        $dimensions_depth = 0;
        $dimensions_weight = 0;

        foreach ($this->article_properties as $article_property) {
            $article = $article_property->article;

            if(!is_null($article->size_x) && !is_null($article->size_y) && !is_null($article->size_z) && !is_null($article->weight)) {
                if($isKg){
                    $article->weight /= 1000; 
                }
                $dimensions_width += $article->size_x * $article_property->pivot->quantity;
                $dimensions_height += $article->size_y * $article_property->pivot->quantity;
                $dimensions_depth += $article->size_z * $article_property->pivot->quantity;
                $dimensions_weight += $article->weight * $article_property->pivot->quantity;
            } else {
                return null;
            }
        }

        /*if(($dimensions_width - intval($dimensions_width)) > 0 || ($dimensions_height - intval($dimensions_height)) > 0 || 
        ($dimensions_depth - intval($dimensions_depth)) > 0 || ($dimensions_weight - intval($dimensions_weight)) > 0) {
            return null;
        }*/

        $dimensions = $dimensions_width . 'x' . $dimensions_height . 'x' . $dimensions_depth . ',' . $dimensions_weight;

        return $dimensions;
    }
}
