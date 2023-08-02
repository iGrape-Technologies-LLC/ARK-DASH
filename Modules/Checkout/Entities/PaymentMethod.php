<?php

namespace Modules\Checkout\Entities;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model {
		protected $fillable = ['id', 'name', 'business_number', 'active', 'is_offline', 'extra_info'];
}
