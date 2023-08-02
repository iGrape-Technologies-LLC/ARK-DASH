<?php

namespace Modules\Shipping\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Shipping\Entities\ShippingMethod;
use Modules\Shipping\Carriers\MercadoenviosCarrier;
use Modules\Shipping\Carriers\AndreaniCarrier;

class ShippingMethodsTableSeeder extends Seeder
{
    private $me;
    private $and;

    public function __construct(MercadoenviosCarrier $me, AndreaniCarrier $and) {
        $this->me = $me;
        $this->and = $and;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ShippingMethod::create([
            'id' => $this->me->getId(),
            'name' => $this->me->getName()
        ]);

        ShippingMethod::create([
            'id' => $this->and->getId(),
            'name' => $this->and->getName(),
            'has_pick_up' => true
        ]);
    }
}
