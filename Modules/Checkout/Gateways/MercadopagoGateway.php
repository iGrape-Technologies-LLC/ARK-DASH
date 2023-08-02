<?php
namespace Modules\Checkout\Gateways;

use App\Models\Transaction;
use Illuminate\Http\Request;
use \GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Modules\Shipping\Carriers\MercadoenviosCarrier;
use MercadoPago;

class MercadopagoGateway extends BaseGateway {
  private $httpClient;
  private $mercadoEnvios;

  public function __construct(Client $client, MercadoenviosCarrier $me) {
    $this->id = 1;
    $this->name = 'Mercado Pago';

    $this->mercadoEnvios = $me;
    $this->httpClient = $client;
  }
  
  public function configure() {
    if(config('app.debug') == 'true') {
      $this->config['access_token'] = config('mercadopago.sandbox_access_token');
    } else {
      $this->config['access_token'] = config('mercadopago.live_access_token');
    }
 
    $this->config['back_urls'] = [
        "success" => route('checkout.paymentprocessed') . '?return_from=' . $this->name . '&status=success',
        "pending" => route('checkout.paymentprocessed') . '?return_from=' . $this->name . '&status=pending',
        "failure" => route('checkout.paymentprocessed') . '?return_from=' . $this->name . '&status=failure',
    ];

    $this->config['payment_methods'] = [
        "installments" => 24,// maximo numero de cuotas
        "default_payment_method_id" => null,// visa, master, amex, naranja, nativa, tarshop, cencosud, cabal, argencard, diners, pagofacil, rapipago, redlink, bapropagos, account_money, cmr, cordial, cordobesa, maestro, debcabal, cargavirtual
        "default_installments" => null,// numero de cuotas por defecto
    ];
  }

	public function process($cart, $transaction, $card) {
    MercadoPago\SDK::setAccessToken($this->config['access_token']);  

    $items = [];
    foreach($cart->article_properties as $article_property) {
    	$item = new MercadoPago\Item();
      $item->title = $article_property->article->title;
      $item->quantity = $article_property->pivot->quantity;
      $item->unit_price = (float)$article_property->final_price;
      $item->currency_id = $article_property->article->currency->code;

      $items[] = $item;
    }

    // sumar envio en caso de que no se haya seleccionado mercado envios
    if($transaction->total_shipping > 0 && !$transaction->has_shipping_method($this->mercadoEnvios->getId())) {
      $item = new MercadoPago\Item();
      $item->title = __('checkout::process.shipping');
      $item->quantity = 1;
      $item->unit_price = $transaction->total_shipping;
      $item->currency_id = 'ARS';

      $items[] = $item;
    }
 
    $preference = new MercadoPago\Preference();
    //Seteo los precios
    $preference->items = $items; 
    //Steo las backs urls     
    $preference->back_urls = $this->config['back_urls'];

    //Seteo autoreturn
    $preference->auto_return = 'approved'; 

    //Seteo external reference
    $preference->notification_url = route('checkout.confirmPayment', [
       'cart_id' => $cart->id,
       'transaction_id' => $transaction->id,
       'gateway_id' => $this->id
    ]);

    //Seteo expires
    $preference->expires = false; 

    //Seteo los metodos
    $preference->payment_methods = $this->config['payment_methods'];

    $payer = new MercadoPago\Payer();
    if(auth()->user() != null) {
      $payer->email = auth()->user()->email;
    }
    if($transaction->has_shipping_method($this->mercadoEnvios->getId())) {
      $payer->address = [
        'zip_code' => $transaction->shipping_address->postal_code,
        'street_name' => $transaction->shipping_address->street,
        'street_number' => $transaction->shipping_address->street_number,
        'floor' => $transaction->shipping_address->floor,
        'apartment' => $transaction->shipping_address->apartment
      ];
    }
    $preference->payer = $payer;

    if($transaction->has_shipping_method($this->mercadoEnvios->getId())) {
      $shippingOptions = new MercadoPago\Shipments();
      $shippingOptions->mode = 'not_specified';
      $shippingOptions->receiver_address = [
        'zip_code' => $transaction->shipping_address->postal_code,
        'street_name' => $transaction->shipping_address->street,
        'street_number' => $transaction->shipping_address->street_number,
        'floor' => $transaction->shipping_address->floor,
        'apartment' => $transaction->shipping_address->apartment
      ];
      $shippingOptions->dimensions = $cart->calculatePackageDimensions();
      $shippingOptions->cost = $transaction->total_shipping;
      $preference->shipments = $shippingOptions;
    }

    $preference->save();

    if(env('APP_DEBUG') == 'true') {
        $init_point = $preference->sandbox_init_point;
    } else {
        $init_point = $preference->init_point;
    }

    return $init_point;
	}

  public function confirmPayment($type, $data_id) {
    if($data_id == null || $type == null || !ctype_digit($data_id)) {
        \Log::info('MP Webhook: Parametros incompletos');
        abort(500);
        return;
    }

    try {
        MercadoPago\SDK::setAccessToken($this->config['access_token']);
        
        if ($type == 'payment') {
          $response = $this->httpClient->request('GET', 'https://api.mercadopago.com/v1/payments/' .  
              $data_id . '?access_token=' . $this->config['access_token']);
          $preference = json_decode($response->getBody(), true);
        } else{
          \Log::info('MP Webhook: El tipo recibido no es payment');
          return null;
        }
        
        if($preference != null) {
            \Log::info('MP Webhook preference: ' . json_encode($preference));
            if ($preference["status"] == 'approved') {
              return $preference;
            } else {
              \Log::info('MP Webhook: El estado de la preference NO es aproved');
              return null;
            }   
        } else {
            \Log::info('MP Webhook: Preferencia no encontrada');
            return null;
        }
    } catch (\Exception $e) {        
        \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        return null;
    }
    
    return null;
  }
}

?>
