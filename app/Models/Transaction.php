<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;

class Transaction extends Model
{
    protected $fillable = ['type', 'user_id', 'cart_id', 'status_id', 'status', 'total_shipping', 'total_discounts', 'total_articles', 
    'total_paid', 'shipping_address_id', 'invoice_address_id', 'receiver_name', 'receiver_doc_number',  'afip_type_id', 'name_invoice',
    'doc_number', 'notes','created_at', 'shipping_type', 'shipping_address_extra'];

    public function user() {
    	return $this->belongsTo('App\User');
    }    

    public function afip_type() {
        return $this->belongsTo('App\Models\AfipType');
    }    

    public function lines() {
    	return $this->hasMany('App\Models\TransactionLine');
    }

    public function internal_status() {
        return $this->belongsTo('App\Models\Status', 'status_id');
    }

    public function payments() {
    	return $this->hasMany('Modules\Checkout\Entities\Payment');
    }

    /* 
    * Valida que los articulos de la venta tengan stock
    * y que esten activos. En caso contrario los quita
    * da error
    */
    public function syncArticlesByStockAndAvailability() {
        $error = false;

        foreach($this->lines as $line) {
            if($line->article_property != null) {
                $article_property = $line->article_property;
                if($article_property->article != null && $article_property->article->active) {
                    if(config('config.MANAGE_STOCK') == 'true') {
                        if(!is_null($article_property->stock) && $article_property->stock < $line->quantity) {
                            $error = true;
                        }
                    }
                } else {
                    $error = true;
                }
            }
        }

        return $error;
    }

    public function paymentStatus(){
        $final_total = $this->total_articles + $this->total_shipping - $this->total_discounts;
        $total_paid = 0;
        foreach ($this->payments as $payment) {
            $total_paid += $payment->amount;
        }
        $status = 'due';
        $epsilon = 0.00001;

        if( abs($total_paid-$final_total) < $epsilon || $total_paid>$final_total) { 
            $status = 'paid';
        }

        return $status;
    }

    public function shippings() {
        return $this->hasMany('Modules\Shipping\Entities\Shipping');
    }

    public function shipping_address() {
        return $this->belongsTo('App\Models\Address');
    }

    public function shipping_name() {
        $shipping = [];
        foreach ($this->shippings as $ship) {
            $shipping[] = __('sells.'.$ship->method->name); 
        }

        if(count($shipping)){
            return implode(' - ', $shipping);
        } else{
            if($this->shipping_type == 'pick_up_store'){
                return __('sells.'.'pick_up_store');
            }
        }
        return __('sells.'.'without_shipping');
    }

    public function payment_name() {
        $payments = [];
        foreach ($this->payments as $payment) {
            $payments[] = $payment->method->name; 
        }

        if(count($payments)){
            return implode(' - ', $payments);
        } else{
            return __('sells.not_payments_yet');
        }

    }

    public function date() {
        return UtilGeneral::format_date($this->created_at);
    }
    
    public function final_total(){
        return $this->total_articles + $this->total_shipping - $this->total_discounts;
    }

    public function final_totalFormatted() {
        return UtilGeneral::number_format($this->final_total());
    }

    public function total_shippingFormatted() {
        return UtilGeneral::number_format($this->total_shipping);
    }

    public function total_discountsFormatted() {
        return UtilGeneral::number_format($this->total_discounts);
    }

    public function total_articlesFormatted() {
        return UtilGeneral::number_format($this->total_articles);
    }

    public function has_shipping_method($shipping_method_id) {
        foreach($this->shippings as $ship) {
            if($ship->shipping_method_id == $shipping_method_id) {
                return true;
            }
        }

        return false;
    }

    public function calculatePackageDimensions() {
        $dimensions_width = 0;
        $dimensions_height = 0;
        $dimensions_depth = 0;
        $dimensions_weight = 0;

        foreach ($this->lines as $line) {
            $article = $line->article_property->article;

            if(!is_null($article->size_x) && !is_null($article->size_y) && !is_null($article->size_z) && !is_null($article->weight)) {
                $dimensions_width += $article->size_x * $line->quantity;
                $dimensions_height += $article->size_y * $line->quantity;
                $dimensions_depth += $article->size_z * $line->quantity;
                $dimensions_weight += $article->weight * $line->quantity;
            } else {
                return null;
            }
        }

        /* if(($dimensions_width - intval($dimensions_width)) > 0 || ($dimensions_height - intval($dimensions_height)) > 0 || 
        ($dimensions_depth - intval($dimensions_depth)) > 0 || ($dimensions_weight - intval($dimensions_weight)) > 0) {
            return null;
        } */

        $dimensions = $dimensions_width . 'x' . $dimensions_height . 'x' . $dimensions_depth . ',' . $dimensions_weight;

        return $dimensions;
    }

    public function getAndreaniTrackingLinksAttribute() {
        $links = [];
        foreach($this->shippings as $shipping) {
            if($shipping->method->name == 'Andreani' && !is_null($shipping->tracking_code)) {
                $links[] = $shipping->tracking_link;
            }
        }

        return $links;
    }
}
