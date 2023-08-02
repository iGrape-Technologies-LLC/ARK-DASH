<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['id', 'name', 'business_number', 'active', 'has_pick_up'];

    public static function forDropdown()
    {
        $categories = ShippingMethod::orderBy('name', 'asc')->get();

        $dropdown =  $categories->pluck('name', 'id');

        return $dropdown;
    }
}
