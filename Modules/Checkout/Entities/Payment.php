<?php

namespace Modules\Checkout\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UtilGeneral;

class Payment extends Model
{
    protected $fillable = ['payment_method_id', 'transaction_id', 'amount', 'status', 'notes', 'token', 'created_at'];

    public function method() {
    	return $this->belongsTo('Modules\Checkout\Entities\PaymentMethod', 'payment_method_id');
    }

    public function date() {
        return UtilGeneral::format_date($this->created_at);
    }

    public function amountFormatted() {
        return UtilGeneral::number_format($this->amount);
    }
}
