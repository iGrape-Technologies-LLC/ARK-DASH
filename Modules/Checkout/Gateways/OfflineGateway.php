<?php
namespace Modules\Checkout\Gateways;

use App\Models\Transaction;
use Illuminate\Http\Request;
use \GuzzleHttp\Client;

class OfflineGateway extends BaseGateway {
	private $httpClient;

	public function __construct(Client $client) {
		$this->id = 2;
		$this->name = 'Pago offline';

		$this->httpClient = $client;
	}

	public function process($cart, $transaction, $card) {
		$response = $this->httpClient->request('POST', route('checkout.confirmPayment', [
			'type' => 'payment',
			'cart_id' => $cart->id,
			'transaction_id' => $transaction->id,
			'gateway_id' => $this->id
		]), [
			'verify' => false,
			'form_params' => [
				'data_id' => $this->id
			]
		]); 
		
		return route('checkout.paymentprocessed', ['status' => 'success']);
	}
}

?>
