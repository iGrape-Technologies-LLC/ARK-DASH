<?php
namespace Modules\Shipping\Carriers;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\ClientException;
use Andreani\Andreani;
use Andreani\Requests\CotizarEnvio;
use Andreani\Requests\ConfirmarCompra;
use Andreani\Requests\ObtenerEstadoDistribucion;
use Andreani\Requests\ObtenerEstadoDistribucionCodificado;
use Andreani\Requests\ObtenerTrazabilidad;
use Andreani\Requests\AnularEnvio;
use Andreani\Requests\ConsultarSucursales;
use App\Utils\UtilGeneral;
use App\User;
use App\Models\Address;

class AndreaniCarrier extends BaseCarrier 
{
		private $username;
		private $passwd;
		private $codigo_cliente;
		private $nro_contrato;
		private $environment;
		private $contrato_sucursal;
		private $contrato_estandar;
		private $base_href;
		private $token;

		private $httpClient;

		public function __construct(Client $httpClient) {
			$this->id = 2;
			$this->name = 'Andreani';
			$this->userEmail = 'sucursales@andreani.com';
			$this->isKg = true;

			$this->httpClient = $httpClient;
		}

		public function configure() {
			$this->username = env('ANDREANI_USERNAME');
			$this->passwd = env('ANDREANI_PASSWD');
			$this->codigo_cliente = env('ANDREANI_CODIGO_CLIENTE');
			$this->environment = env('ANDREANI_ENV');
			$this->contrato_sucursal = env('ANDREANI_CONTRATO_SUCURSAL');
			$this->contrato_estandar = env('ANDREANI_CONTRATO_ESTANDAR');
			$this->token = '';
			if($this->environment == 'test') {
				$this->base_href = 'https://api.qa.andreani.com';
			} else {
				$this->base_href = 'https://api.andreani.com';
			}

			$this->login();
		}

		public function searchOptions($dimensions, $zipcode, $price, $pick_up=false) {
			if($pick_up) {
				$nro_contrato = $this->contrato_sucursal;
			} else {
				$nro_contrato = $this->contrato_estandar;
			}

			$packageData = $this->packageDataFromDimensions($dimensions);

			$request_options = [
				'headers' => [
	        'X-Authorization-token' => $this->token
	      ],
	      'query' => [
	      	'cpDestino' => $zipcode,
	      	'contrato' => $nro_contrato,
	      	'cliente' => $this->codigo_cliente,
	      	'bultos[0][valorDeclarado]' => $price,
	      	'bultos[0][volumen]' => $packageData['volumen'],
	      	'bultos[0][kilos]' => $packageData['peso']
	      ]
			];
			$response = null;
			try {
				$response = $this->httpClient->request('GET', $this->base_href . '/v1/tarifas', $request_options);
			} catch(\Exception $e) {
				\Log::debug($e);
				return null;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201)) {
				$prices = json_decode($response->getBody(), true);

				if($prices != null && isset($prices['tarifaConIva']) && isset($prices['tarifaConIva']['total'])) {
					return [
	        	'carrier_id' => $this->id,
	        	'name' => 'Envio estándar',
	        	'cost' => (float)$prices['tarifaConIva']['total'],
	        	'currency' => 'ARS'
	        ];
				} else {
					return null;
				}
			} else {
				return null;
			}
		}

		private function login() {
			$request_options = [
				'headers' => [
	        'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->passwd)
	      ]
			];
			$response = null;
			try {
				$response = $this->httpClient->request('GET', $this->base_href . '/login', $request_options);
			} catch(\Exception $e) {
				\Log::debug($e);
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) && 
			$response->hasHeader('X-Authorization-token')) {
				$this->token = $response->getHeader('X-Authorization-token')[0];
			}
		}

		public function ship($transaction, $dimensions, $pick_up=false) {
			$address = $transaction->shipping_address;
			$user = $transaction->user;
			$packageData = $this->packageDataFromDimensions($dimensions);
			if($pick_up) {
				$nro_contrato = $this->contrato_sucursal;
			} else {
				$nro_contrato = $this->contrato_estandar;
			}

			$order_data = [
				//'verify' => false,
				'headers' => [
					'x-authorization-token' => $this->token
				],
				'json' => [
					'contrato' => $nro_contrato,
					'origen' => [
						'postal' => [
							'codigoPostal' => config('business.address.postal_code'),
							'calle' => config('business.address.street'),
	            "numero" => config('business.address.street_number'),
	            "localidad" => config('business.city'),
	            "region" => "",
	            "pais" => config('business.country')
						]
					],
					'destino' => [
						'postal' => [
							'codigoPostal' => $address->postal_code,
							'calle' => $address->street,
							'numero' => strval($address->street_number),
							'localidad' => $address->city,
							'region' => '',
							'pais' => config('business.country'),
							'componentesDeDireccion' => [
								[
									'meta' => 'PisoDepto',
									'contenido' => !is_null($address->floor) ? $address->floor : ''
								]
							]
						]
					],
					'remitente' => [
						'nombreCompleto' => config('business.owner'),
						'email' => config('business.email'),
						'documentoTipo' => config('business.tax_id_type'),
						'documentoNumero' => config('business.tax_id'),
						'telefonos' => [
							[
								'tipo' => 1,
								'numero' => config('business.phone')
							]
						]
					],
					'destinatario' => [
						[
							'nombreCompleto' => $user->fullname(),
							'email' => $user->email,
							'documentoTipo' => $user->doc_type,
							'documentoNumero' => $user->doc_number,
							'telefonos' => [
								[
									'tipo' => 1,
									'numero' => $user->phone
								]
							]
						]
					],
					'producto a entregar' => 'Compra online en ' . config('business.name'),
					'bultos' => [
						[
							'kilos' => isset($packageData['peso']) ? $packageData['peso'] : 0,
							'volumenCm' => isset($packageData['volumen']) ? $packageData['volumen'] : 0,
							'largoCm' => isset($packageData['largo']) ? $packageData['largo'] : 0,
							'altoCm' => isset($packageData['alto']) ? $packageData['alto'] : 0,
							'anchoCm' => isset($packageData['ancho']) ? $packageData['ancho'] : 0
						]
					]
				]
			];

			$response = null;
			try {
				$response = $this->httpClient->post($this->base_href . '/v2/ordenes-de-envio', $order_data);
			} catch(\Exception $e) {
				\Log::debug($e);
				return null;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201 || 
			$response->getStatusCode() == 202)) {
				$shipment = json_decode($response->getBody(), true);
				\Log::debug($shipment['bultos'][0]['numeroDeEnvio']);
				
				if(!is_null($shipment) && isset($shipment['bultos']) && count($shipment['bultos']) > 0) {
					return $shipment['bultos'][0]['numeroDeEnvio'];
				} else {
					return null;
				}
			}
		}

		public function getShipmentStatus($shipment_id) {
			$request_options = [
				'headers' => [
			        'X-Authorization-token' => $this->token
			      ]
			];

			$response = null;
			try {
				$response = $this->httpClient->request('GET', $this->base_href . '/v1/envios/' . $shipment_id . '/trazas', 
					$request_options);
			} catch(ClientException $e) {
				if($e->getCode() == 404) {
					\Log::debug('Shipment ' . $shipment_id . ' no encontrado');
				} else {
					\Log::debug($e);
				}

				return null;
			} catch(\Exception $e) {
				\Log::debug($e);
				return null;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201 || 
			$response->getStatusCode() == 202)) {
				$parsed_response = json_decode($response->getBody(), true);
				$events = isset($parsed_response['eventos']) ? $parsed_response['eventos'] : [];
				
				if(count($events) > 0) {
					// devuelve el ultimo evento
					$last_event = $events[count($events)-1];
					
					return isset($last_event['Estado']) ? $last_event['Estado'] : null;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}

		public function cancel($shipment_id) {
			$username = env('ANDREANI_USERNAME');
			$passwd = env('ANDREANI_PASSWD');
			$environment = env('ANDREANI_ENV');

			$request = new AnularEnvio();
			$request->setNumeroDeEnvio($shipment_id);

			$andreani = new Andreani($username, $passwd, $environment);
			$response = null;
			try {
				$response = $andreani->call($request);
			} catch(\Exception $e) {
				return null;
			}

			if($response != null && $response->isValid()) {
        	\Log::debug(json_encode($response->getMessage()));
      } else {
          \Log::debug('error en call: ' . $response->getMessage());
          return null;
      }
		}

		public function findLocations($zip_code, $cart_total, $dimensions = '') {
			$request_options = [
				'headers' => [
	        'X-Authorization-token' => $this->token
	      ],
	      'query' => [
	      	'codigoPostal' => $zip_code,
	      	'seHaceAtencionAlCliente' => 'true'
	      ]
			];
			$response = null;
			try {
				$response = $this->httpClient->request('GET', $this->base_href . '/v2/sucursales', $request_options);
			} catch(\Exception $e) {
				\Log::debug($e);
				return null;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201)) {
				$locations = [];
				$sucursales = json_decode($response->getBody(), true);
				foreach($sucursales as $sucursal) {
					$location = [];
					$location['id'] = isset($sucursal['id']) ? $sucursal['id'] : '';
					$location['name'] = ucfirst(strtolower(trim($sucursal['descripcion'])));
					$location['street'] = isset($sucursal['direccion']) ? trim($sucursal['direccion']['calle']) : '';
					$location['street_number'] = isset($sucursal['direccion']) ? trim($sucursal['direccion']['numero']) : '';
					$location['zip'] = isset($sucursal['direccion']) ? trim($sucursal['direccion']['codigoPostal']) : '';
					$location['city'] = isset($sucursal['direccion']) ? trim($sucursal['direccion']['localidad']) : '';
					$location['state'] = isset($sucursal['direccion']) ? trim($sucursal['direccion']['provincia']) : '';
					$location['country'] = isset($sucursal['direccion']) ? trim($sucursal['direccion']['pais']) : '';

					$shipment = $this->searchOptions($dimensions, $location['zip'], $cart_total, true);
					$location['cost_formatted'] = UtilGeneral::number_format($shipment['cost']);
					$location['cost'] = $shipment['cost'];

					$locations[] = $location;
				}

				return $locations;
			} else {
				return null;
			}
		}

		public function getTicket($tracking_code) {
			$request_options = [
				'headers' => [
	        'X-Authorization-token' => $this->token
	      ]
			];
			$response = null;
			try {
				$response = $this->httpClient->request('GET', $this->base_href . '/v2/ordenes-de-envio/' . $tracking_code . 
					'/etiquetas', $request_options);
			} catch(\Exception $e) {
				\Log::debug($e);
				return null;
			}

			if($response != null && ($response->getStatusCode() == 200 || $response->getStatusCode() == 201)) {
				return $response->getBody();
			} else {
				return null;
			}
		}

		// calcula peso y volumen a partir de la estructura altoXanchoXlargo,peso
		private function packageDataFromDimensions($dimensions) {
			$peso = 0.0;
			$volumen = 1.0;
			$sumaAlto = 0.0;
			$sumaAncho = 0.0;
			$sumaLargo = 0.0;

			$dimensions_parts = explode(',', $dimensions);
			if(count($dimensions_parts) == 2) {
				$peso = (float)$dimensions_parts[1];

				$volumen_parts = explode('x', $dimensions_parts[0]);
				if(count($volumen_parts) > 2) {
					$sumaAlto += (float)$volumen_parts[0];
					$sumaAncho += (float)$volumen_parts[1];
					$sumaLargo += (float)$volumen_parts[2];

					foreach($volumen_parts as $vol) {
						$volumen = $volumen * (float)$vol;
					}
				}
			} else {
				return null;
			}

			return [
				'peso' => $peso,
				'volumen' => $volumen,
				'alto' => $sumaAlto,
				'ancho' => $sumaAncho,
				'largo' => $sumaLargo
			];
		}

		// crea las sucursales del carrier como direcciones de un usuario que representa el carrier
		public function createLocations($locations) {
			$addresses = [];

			if(count($locations) > 0) {
          $userAndreani = User::where('email', $this->userEmail)->first();
          if(!is_null($userAndreani)) {
              foreach ($locations as $location) {
                  $foundAddress = Address::where('address_extra', $location['id'])
                      ->where('user_id', $userAndreani->id)
                      ->first();

                  if(is_null($foundAddress)) {
                  	$add = Address::create([
                  		'name' => isset($location['name']) ? $location['name'] : '',
                  		'street' => isset($location['street']) ? $location['street'] : '',
                  		'street_number' => isset($location['street_number']) ? $location['street_number'] : '',
                  		'postal_code' => isset($location['zip']) ? $location['zip'] : '',
                  		'address_extra' => isset($location['id']) ? $location['id'] : '',
                  		'city' => isset($location['city']) ? $location['city'] : '',
                  		'user_id' => $userAndreani->id
                  	]);
                  	$location['address_id'] = $add->id;
                  	$addresses[] = $location;
                  } else {
                  	$location['address_id'] = $foundAddress->id;
                  	$addresses[] = $location;
                  }
              }
          }
      }

			return $addresses;
		}
}

?>
