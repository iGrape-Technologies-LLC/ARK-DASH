<?php
namespace Modules\Shipping\Carriers;

use \GuzzleHttp\Client;

class MercadoenviosCarrier extends BaseCarrier 
{
	private $httpClient;

	public function __construct(Client $client) {
		$this->httpClient = $client;

		$this->id = 1;
		$this->name = 'Mercado Envios';
		$this->isKg = false;
	}

	public function searchOptions($dimensions, $zipcode, $price, $pick_up=false) {
			if(env('APP_DEBUG') == 'true') {
		    $access_token = env('MERCADOPAGO_SANDBOX_ACCESSTOKEN');
		  } else {
		  	$access_token = env('MERCADOPAGO_LIVE_ACCESSTOKEN');
		  }

		  if($access_token == null) {
		  	return null;
		  }
	    
	    $response = $this->httpClient->request('GET', 'https://api.mercadopago.com/shipping_options?access_token=' . 
	        $access_token . '&dimensions=' . $dimensions . '&zip_code=' . $zipcode . '&item_price=' . $price, [
	        'headers' => [
	            'User-Agent' => 'GuzzleHttp/1.0',
	            'Accept'     => 'application/json',
	            ]
	        ]);

	    if($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
	    	try {
	    		$body = json_decode($response->getBody(), true);
	    	} catch(\Exception $e) {
	    		return null;
	    	}
	    	if(count($body['options'])) {
	    		$option = $body['options'][0];
	    		return [
	       		'carrier_id' => $this->id,
	       		'name' => $option['name'],
	       		'cost' => $option['cost'],
	       		'currency' => $option['currency_id']
	       	];
	    	} else {
	    		return null;
	    	}
	    } else {
        return null;
	    }
	}

	public function ship($transaction, $dimensions, $pick_up=false) {
			if($transaction == null) {
				return null;
			}

			if(env('APP_DEBUG') == 'true') {
		    $access_token = env('MERCADOPAGO_SANDBOX_ACCESSTOKEN');
		  } else {
		  	$access_token = env('MERCADOPAGO_LIVE_ACCESSTOKEN');
		  }

		  $response = $this->httpClient->request('GET', 'https://api.mercadopago.com/merchant_orders/' . 
		  		$transaction . '?access_token=' . $access_token, [
	        'headers' => [
	            'User-Agent' => 'GuzzleHttp/1.0',
	            'Accept'     => 'application/json',
	            ]
	        ]);

		 	if($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
	 			try {
	 				$body = json_decode($response->getBody(), true);
	 			} catch(\Exception $e) {
	 				return null;
	 			}

	 			if(isset($body['shipments']) && count($body['shipments']) > 0 && isset($body['shipments'][0]['id'])) {
	 				return $body['shipments'][0]['id'];
	 			} else {
	 				return null;
	 			}
		 	} else {
		 		return null;
		 	}
	}

	public function getShipmentStatus($shipment_id) {
		if(env('APP_DEBUG') == 'true') {
		    $access_token = env('MERCADOPAGO_SANDBOX_ACCESSTOKEN');
		  } else {
		  	$access_token = env('MERCADOPAGO_LIVE_ACCESSTOKEN');
		  }

		  $response = $this->httpClient->request('GET', 'https://api.mercadolibre.com/shipments/' . 
		  		$shipment_id . '?access_token=' . $access_token, [
	        'headers' => [
	            'User-Agent' => 'GuzzleHttp/1.0',
	            'Accept'     => 'application/json',
	            ]
	        ]);

		 	if($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
		 		try {
	 				$body = json_decode($response->getBody(), true);
	 			} catch(\Exception $e) {
	 				return null;
	 			}

	 			if(isset($body['status'])) {
	 				return $body['status'];
	 			} else {
	 				return null;
	 			}
		 	} else {
		 		return null;
		 	}
	}
}

?>
