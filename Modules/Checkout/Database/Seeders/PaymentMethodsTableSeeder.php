<?php

namespace Modules\Checkout\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Checkout\Entities\PaymentMethod;
use Modules\Checkout\Gateways\MercadopagoGateway;
use Modules\Checkout\Gateways\OfflineGateway;
use Modules\Checkout\Gateways\DecidirGateway;
use Modules\Checkout\Gateways\PagoFacilGateway;

class PaymentMethodsTableSeeder extends Seeder
{
    private $mpg;
    private $offline;
    private $decidir;
    private $pf;

    public function __construct(MercadopagoGateway $mpg, OfflineGateway $offline, DecidirGateway $decidir, PagoFacilGateway $pf) {
        $this->mpg = $mpg;
        $this->offline = $offline;
        $this->decidir = $decidir;
        $this->pf = $pf;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::create([
            'id' => $this->mpg->getId(),
            'name' => $this->mpg->getName()
        ]);

        PaymentMethod::create([
            'id' => $this->offline->getId(),
            'name' => $this->offline->getName(),
            'is_offline' => true,
            'extra_info' => '<div class="row mt-30">
    <div class="col-12 col-md-4">
      <strong>Efectivo</strong>
      <p>Para pago en efectivo, acercate a alguna de nuestras sucursales</p>
    </div>
    <div class="col-12 col-md-4">
      <strong>Transferencia</strong>
      <p>Tranferinos al CBU 817239871823 y envianos el comprobante</p>
    </div>
    <div class="col-12 col-md-4">
      <strong>Para pago en efectivo, acercate a alguna de nuestras sucursales</strong>
      <p>Envianos un cheque a alguna de nuestras sucursales</p>
    </div>
  </div>'
        ]);

        PaymentMethod::create([
            'id' => $this->decidir->getId(),
            'name' => $this->decidir->getName(),
            'extra_info' => '#card-form'
        ]);

        PaymentMethod::create([
            'id' => $this->pf->getId(),
            'name' => $this->pf->getName(),
            'is_offline' => true,
            'active' => false
        ]);
    }
}
