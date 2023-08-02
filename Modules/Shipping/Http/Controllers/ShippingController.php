<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use \GuzzleHttp\Client;
use Modules\Shipping\Carriers\MercadoenviosCarrier;
use Modules\Shipping\Carriers\AndreaniCarrier;
use Modules\Checkout\Gateways\MercadopagoGateway;
use Modules\Checkout\Entities\PaymentMethod;
use Modules\Checkout\Entities\Cart;
use Modules\Shipping\Entities\Shipping;
use App\Models\Address;
use Storage;

class ShippingController extends Controller 
{
    private $httpClient;
    private $carriers;
    private $addressRepository;
    private $paymentMethodRepository;
    private $shippingRepository;
    private $cartRepository;
    private $mercadoEnvios;
    private $mercadoPago;

    public function __construct(Client $client, Address $address, PaymentMethod $paymentMethod, Shipping $shipping, Cart $cart,
        MercadoenviosCarrier $me, MercadopagoGateway $mp, AndreaniCarrier $and) {
        $this->httpClient = $client;
        $this->addressRepository = $address;
        $this->paymentMethodRepository = $paymentMethod;
        $this->shippingRepository = $shipping;
        $this->cartRepository = $cart;

        $this->mercadoEnvios = $me;
        $this->mercadoPago = $mp;

        $this->carriers[$me->getId()] = $me;
        $this->carriers[$and->getId()] = $and;        
    }

    public function searchOptions(Request $request) {
        $request->validate([
            'carrier_id' => ['required', 'numeric', 'min:1'],
            'cart_id' => ['required', 'numeric', 'exists:carts,id'],
            'address_id' => ['required', 'numeric']
        ]);

        $address = $this->addressRepository->find($request->input('address_id'));

        if(isset($this->carriers[$request->input('carrier_id')]) && $address != null && session()->has('cart')) {
            $carrier = $this->carriers[$request->input('carrier_id')];
            $cart = $this->cartRepository->find($request->input('cart_id'));
            $dimensions = $cart->calculatePackageDimensions($carrier->getIsKg());
            if($dimensions != null) {
                if(!is_numeric($address->postal_code)) {
                    abort(422, __('shipping::general.numeric_zip_code'));
                }

                $price = $cart->total;

                $carrier->configure();
                $options = $carrier->searchOptions($dimensions, $address->postal_code, $price);

                if($options != null) {
                    return $options;
                } else {
                    abort(500, __('shipping::general.no_tolls'));
                }
            } else {
                abort(500, __('shipping::general.wrong_dimensions'));
            }
        } else {
            abort(404, 'Carrier not found or invalid address');
        }
    }

    public function carrierAvailablePaymentMethods(Request $request) {
        $request->validate([
            'carrier_id' => ['required', 'numeric']
        ]);

        if($request->input('carrier_id') == $this->mercadoEnvios->getId()) {
            return [$this->mercadoPago->getId()];
        } else {
            $paymentMethods = $this->paymentMethodRepository->all()->pluck('id')->toArray();

            return $paymentMethods;
        }
    }

    public function carrierLocations(Request $request) {
        $request->validate([
            'carrier_id' => ['required', 'numeric'],
            'zip_code' => ['required'],
            'cart_total' => ['required', 'numeric']
        ]);

        if(isset($this->carriers[$request->query('carrier_id')])) {
            $carrier = $this->carriers[$request->query('carrier_id')];
            $carrier->configure();

            $user = auth()->user();
            $cart = $this->cartRepository->userCurrentCart($user);
            $dimensions = $cart->calculatePackageDimensions($carrier->getIsKg());

            $locations = $carrier->findLocations($request->query('zip_code'), $request->query('cart_total'), $dimensions);
            $locations = $carrier->createLocations($locations);
            $carrier_id = $carrier->getId();

            if($locations != null) {
                return view('shipping::partials.carrier_locations', compact('locations', 'carrier_id'));
            } else {
                return view('shipping::partials.carrier_locations', ['locations' => []]);
            }
        } else {
            abort(404);
        }
    }

    public function updateShipmentsStatus() {
        \Log::info('Updating shipments status');
        $shipments = $this->shippingRepository
                        ->whereNotNull('tracking_code')
                        ->where('status', '<>', 'delivered')
                        ->where('status', '<>', 'cancelled')
                        ->get();

        foreach ($shipments as $shipment) {
            if(isset($this->carriers[$shipment->shipping_method_id])) {
                $carrier = $this->carriers[$shipment->shipping_method_id];
                $carrier->configure();
                $status = $carrier->getShipmentStatus($shipment->tracking_code);

                if($status != null) {
                    $shipment->update([
                        'status' => $status
                    ]);
                }
            }
        }
    }

    public function ship($transaction, $payment) {
        $transaction_shippings = $transaction->shippings;

        if(count($transaction_shippings) > 0 && isset($this->carriers[$transaction_shippings[0]->shipping_method_id])) {
            $carrier = $this->carriers[$transaction_shippings[0]->shipping_method_id];
            $carrier->configure();

            $dimensions = $transaction->calculatePackageDimensions($carrier->getIsKg());
            $ispickup = $transaction->shipping_type == 'address' ? false : true;

            $tracking_code = $carrier->ship($transaction, $dimensions, $ispickup);

            if($transaction_shippings[0]->tracking_code == null) {
                $transaction_shippings[0]->update([
                    'tracking_code' => $tracking_code
                ]);
            }
        }
    }

    public function getTicket($shipment_id) {
        $shipment = $this->shippingRepository->findOrFail($shipment_id);

        if(isset($this->carriers[$shipment->shipping_method_id])) {
            $carrier = $this->carriers[$shipment->shipping_method_id];
            $carrier->configure();
            $ticketcontent = $carrier->getTicket($shipment->tracking_code);
            Storage::disk('local')->put('ticket_envio.pdf', $ticketcontent);
            $file = storage_path('app'). "/ticket_envio.pdf";

            $headers = array(
                'Content-Type: application/pdf',
            );

            return response()->download($file, 'ticket.pdf', $headers);
        } else {
            abort(404);
        }
    }
}
