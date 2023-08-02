<?php
namespace Modules\Checkout\Gateways;

use Illuminate\Http\Request;

abstract class BaseGateway
{
    protected $id;
    protected $name;
    protected $config = [];

    public function configure() {
        
    }

    public function setConfig($key, $value) {
        $this->config[$key] = $value;
    }

    /**
     * @param \Modules\Booking\Entities\Cart $cart
     * @param \App\Models\Transaction $transaction
     */
    public function process($cart, $transaction, $card)
    {

    }

    /**
     * @param Illuminate\Http\Request $request
     */
    public function cancelPayment(Request $request)
    {

    }

    /**
     * @param string $type
     * @param string $data_id
     */
    public function confirmPayment(string $type, string $data_id)
    {
        return true;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }
}
