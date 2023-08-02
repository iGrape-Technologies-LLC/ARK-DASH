<?php
namespace Modules\Checkout\Gateways;

use Illuminate\Http\Request;
use \GuzzleHttp\Client;

class PagoFacilGateway extends BaseGateway {

	private $httpClient;
	private $keys_data;
	private $ambient;
	private $endpoint;

	public function __construct(Client $httpClient) {
		$this->httpClient = $httpClient;

		$this->id = 4;
		$this->name = 'Pago Facil';
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
		$connector = new \Decidir\Connector($this->keys_data, $this->ambient);

		$token = $this->createToken($transaction);

		if($token != null && isset($token['id'])) {
			\Log::info('TODO: Cambiar site_transaction_id por $transaction->id cuando tengamos nuestras propias credenciales');
			$site_transaction_id = $transaction->id . date('His');
			$fecha_hoy = new \DateTime();
			$fecha_vencimiento = $fecha_hoy->add(new \DateInterval('P10D'));

			$data = array(
	      "site_transaction_id" => $site_transaction_id,
	      "token" => $token['id'],
	      "payment_method_id" => 26,
	      "amount" => number_format($transaction->total_paid, 2, '.', ''),
	      "currency" => "ARS",
	      "payment_type" => "single",
	      "email" => $transaction->user->email,
	      'invoice_expiration' => $fecha_vencimiento->format('ymd'),
	      'cod_p3' => '00',
	      'cod_p4' => '123',
	      'surcharge' => 3,
	      'client' => $transaction->user->doc_number,
	      'payment_mode' => 'offline'
	    );

	    \Log::debug('trying to create offline payment: ' . json_encode($data));

			try {
				$executedPayment = $connector->payment()->ExecutePaymentOffline($data);

				if($executedPayment != null && $executedPayment->getStatus() == 'invoice_generated') {
					\Log::debug('payment status: ' . $executedPayment->getBarcode());

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

					return route('checkout.offlinereceipt', ['barcode' => $executedPayment->getBarcode()]);
				}
			} catch(\Decidir\Exception\SdkException $e) {
				\Log::debug($e->getData());
			} catch(\Exception $e) {
				\Log::debug($e);
			}
		}

		return route('checkout.paymentprocessed', ['status' => 'fail']);
	}

	private function createToken($transaction) {
		$response = null;

		try {
			$response = $this->httpClient->request('POST', $this->endpoint . '/tokens', [
				'json' => [
					'customer' => [
						'name' => $transaction->user->fullName(),
						'identification' => [
							'type' => 'dni',
							'number' => $transaction->user->doc_number
						]
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
			return null;
		}

		if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201)) {
			$token = json_decode($response->getBody(), true);

			if(isset($token['id'])) {
				return $token;
			}
		}

		return null;
	}
}
?>
