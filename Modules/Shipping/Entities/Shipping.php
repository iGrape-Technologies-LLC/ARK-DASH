<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;

class Shipping extends Model
{
   
    protected $fillable = ['amount', 'tracking_code', 'status', 'shipping_method_id', 'transaction_id'];

    public function transaction() {
    	return $this->belongsTo('App\Models\Transaction');
    }

    public function method() {
    	return $this->belongsTo('Modules\Shipping\Entities\ShippingMethod', 'shipping_method_id');
    }

    public function amountFormatted() {
        return UtilGeneral::number_format($this->amount);
    }

    public function getTrackingLinkAttribute() {
        if($this->method->name == 'Andreani' && !is_null($this->tracking_code)) {
            return 'https://usuarios.e-andreani.com/#!/informacionEnvio/' . $this->tracking_code;
        }
    }
}
