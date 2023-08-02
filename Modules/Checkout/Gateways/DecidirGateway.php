<?php
namespace Modules\Checkout\Gateways;

use Illuminate\Http\Request;
use \GuzzleHttp\Client;
use App\Models\Transaction;
use App\User;

class DecidirGateway extends BaseGateway {

		private $httpClient;
		private $keys_data;
		private $ambient;
		private $endpoint;

		public function __construct(Client $client) {
			$this->httpClient = $client;

			$this->id = 3;
			$this->name = 'Tarjeta de Credito';
		}

		public function configure() {
			$this->keys_data = array(
				'public_key' => env('DECIDIR_PUBLIC_KEY'),
        'private_key' => env('DECIDIR_PRIVATE_KEY')
      );
			$this->ambient = env("DECIDIR_AMBIENT"); 

			$this->endpoint = 'https://live.decidir.com/api/v2';
			if($this->ambient == 'test') {
				$this->endpoint = 'https://developers.decidir.com/api/v2';
			}
		}

		public function process($cart, $transaction, $card) {
	    $status = 'fail';

			$connector = new \Decidir\Connector($this->keys_data, $this->ambient);

			$card = $this->createCardToken($card);

			if(isset($card['id'])) {
				\Log::info('TODO: Cambiar site_transaction_id por $transaction->id cuando tengamos nuestras propias credenciales');
				$site_transaction_id = "transaction_id=" . $transaction->id . date('YmdHis');
				if(env('APP_DEBUG') == true) {
					$site_transaction_id .= '_test';
				}

				$data = array(
		      "site_transaction_id" => $site_transaction_id,
		      "token" => $card['id'],
		      "customer" => array(
	          "id" => (string)$transaction->user->id, 
	          "email" => $transaction->user->email
	      	),
		      "payment_method_id" => 1,
		      "bin" => substr($card['card_number'], 0, 6),
		      "amount" => number_format($transaction->total_paid, 2, '.', ''),
		      "currency" => "ARS",
		      "installments" => 1,
		      "description" => "",
		      "establishment_name" => config('config.APP_NAME'),
		      "payment_type" => "single",
		      "sub_payments" => array()
		    );

		    try {
		    	$executedPayment = $connector->payment()->ExecutePayment($data);

		    	if($executedPayment->getStatus() == 'approved') {
		    		$status = 'success';
		    	}
		    } catch(\Exception $e) {
		    	\Log::error($e);
		    }

		    if($status == 'success') {
		    	try {
	    			$this->httpClient->request('POST', route('checkout.confirmPayment', [
							'type' => 'payment',
							'cart_id' => $cart->id,
							'transaction_id' => $transaction->id,
							'gateway_id' => $this->id
						]), [
							'verify' => false,
							'form_params' => [
								'data_id' => $executedPayment->getId()
							]
						]);
		    	} catch(\Exception $e) {
		    		\Log::debug($e);
		    	}
		    }
			}

	    return route('checkout.paymentprocessed', ['status' => $status]);
		}

		public function confirmPayment($type, $data_id) {
			$response = null;
			try {
				$response = $this->httpClient->request('GET', $this->endpoint . '/payments/' . $data_id, [
					'headers' => [
		        'apikey' => $this->keys_data['private_key'],
		        'Content-Type' => 'application/json',
		        'Cache-Control' => 'no-cache'
		      ]
				]);
			} catch(\Exception $e) {
				\Log::debug($e);

				return null;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode == 201)) {
				$payment = json_decode($response->getBody(), true);

				if(isset($payment['status']) && $payment['status'] == 'approved') {
					return $payment;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}

		public function createCardToken($cardData) {
			$response = null;

			try {
				$response = $this->httpClient->request('POST', $this->endpoint . '/tokens', [
					'json' => [
						'card_number' => $cardData['card_number'],
						'card_expiration_month' => $cardData['card_expiration_month'],
						'card_expiration_year' => $cardData['card_expiration_year'],
						'security_code' => $cardData['security_code'],
						'card_holder_name' => $cardData['card_holder_name'],
						'card_holder_identification' => [
							'type' => $cardData['card_holder_tax_id_type'],
							'number' => $cardData['card_holder_tax_id']
						]
					],
					'headers' => [
            'apikey' => $this->keys_data['public_key'],
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache'
          ]
				]);
			} catch(\Exception $e) {
				\Log::debug($e);
				return $cardData;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201)) {
				$token = json_decode($response->getBody(), true);

				$cardData['id'] = $token['id'];
			}

			return $cardData;
		}
}

?>
