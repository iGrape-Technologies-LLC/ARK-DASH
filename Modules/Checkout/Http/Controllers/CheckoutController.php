<?php

namespace Modules\Checkout\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Checkout\Entities\Cart;
use Modules\Checkout\Entities\Payment;
use Modules\Checkout\Entities\PaymentMethod;
use Modules\Checkout\Gateways\MercadopagoGateway;
use Modules\Checkout\Gateways\OfflineGateway;
use Modules\Checkout\Gateways\DecidirGateway;
use Modules\Checkout\Gateways\PagoFacilGateway;
use Modules\Shipping\Carriers\MercadoenviosCarrier;
use Modules\Shipping\Carriers\AndreaniCarrier;
use Modules\Shipping\Entities\Shipping;
use Modules\Shipping\Http\Controllers\ShippingController;
use App\Models\Address;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\User;
use App\Utils\TransactionUtil;
use App\Mail\NewSellToAdmin;
use App\Mail\NewSellToCustomer;
use Illuminate\Support\Facades\Mail;
use Andreani\Andreani;
use Andreani\Requests\CotizarEnvio;
use Andreani\Requests\ConfirmarCompra;

class CheckoutController extends Controller {

    private $cartRepository;
    private $paymentMethodsRepository;
    private $transactionsRepository;
    private $transactionLinesRepository;
    private $addressRepository;
    private $shippingRepository;
    private $gateways = [];
    private $carriers = [];
    private $shippingController;
    protected $transactionUtil;

    public function __construct(Cart $cartRepository, PaymentMethod $paymentMethod, Transaction $transaction, TransactionLine $transactionLine, 
        Payment $payment, MercadopagoGateway $mp, OfflineGateway $offline, TransactionUtil $transactionUtil, MercadoenviosCarrier $me, 
        AndreaniCarrier $andr, Address $address, Shipping $ship, ShippingController $shipControlller, DecidirGateway $decidir,
        PagoFacilGateway $pf) {
        $this->cartRepository = $cartRepository;
        $this->paymentMethodsRepository = $paymentMethod;
        $this->transactionsRepository = $transaction;
        $this->transactionLinesRepository = $transactionLine;
        $this->paymentRepository = $payment;
        $this->addressRepository = $address;
        $this->shippingRepository = $ship;

        $this->shippingController = $shipControlller;
        $this->transactionUtil = $transactionUtil;

        $this->gateways[$mp->getId()] = $mp;
        $this->gateways[$offline->getId()] = $offline;
        $this->gateways[$decidir->getId()] = $decidir;
        $this->gateways[$pf->getId()] = $pf;

        $this->carriers[$me->getId()] = $me;
        $this->carriers[$andr->getId()] = $andr;
    }       

    public function processPayment(Request $request) {
        if($request->ajax()) {
            $request->validate([
                'shipping_address_id' => ['nullable', 'numeric'],        
                'gateway_id' => ['required', 'numeric'],                
                'shipping_method_id' => ['nullable', 'numeric'],
                'afip_type_id' => ['nullable', 'numeric'],
                'doc_number' => ['nullable', 'string', 'max:255'],
                'note' => ['nullable', 'string', 'max:255'],
                'name_invoice' => ['nullable', 'string', 'max:255'],
                'note' => ['nullable', 'string'],
                'card' => ['nullable', 'array'],
                'card.card_number' => ['nullable', 'min:15'],
                'card.card_expiration_month' => ['nullable', 'min:2'],
                'card.card_expiration_year' => ['nullable', 'min:2'],
                'shipping_type' => ['required', 'string'],
                'shipping_address_extra' => ['nullable', 'string'],
            ]);

            $gateway_id = $request->input('gateway_id');
            $shipping_method_id = $request->input('shipping_method_id');

            if(isset($this->gateways[$gateway_id])) {
                $gateway = $this->gateways[$gateway_id];

                $user = auth()->user();

                $cart = $this->cartRepository->userCurrentCart($user);

                if($cart == null) {
                    return ['success' => false, 'msg' => __('checkout::cart.unexistent')];
                } 

                $cart_changed = $cart->syncArticlesByStockAndAvailability();
                if($cart_changed) {
                    return ['success' => false, 'msg' => __('checkout::process.articles_changed')];
                }

                if(count($cart->article_properties) == 0) {
                    return ['success' => 'false', 'msg' => __('checkout::process.no_articles')];
                }

                $card = $request->input('card');
                if($card != null) {
                    $card['card_holder_name'] = $user->fullName();
                    $card['card_holder_tax_id_type'] = 'dni';
                    $card['card_holder_tax_id'] = $request->input('doc_number');
                }

                $address = null;
                $shipping_zipcode = null;
                if($request->input('shipping_address_id') != null) {
                    $address = $this->addressRepository->find($request->input('shipping_address_id'));
                    $shipping_zipcode = $address->postal_code;
                } else {
                    $shipping_zipcode = $request->input('shipping_extra_zip');
                }

                $gateway->configure();

                DB::beginTransaction();

                $total_paid = $cart->total;

                if($shipping_method_id != null && isset($this->carriers[$shipping_method_id]) && $shipping_zipcode != null) {
                     $carrier = $this->carriers[$shipping_method_id];
                    $dimensions = $cart->calculatePackageDimensions($carrier->getIsKg());

                    if($dimensions != null) {                        

                        $is_pickup = false;
                        if($request->input('shipping_type') != 'address') {
                            $is_pickup = true;
                        }

                        $carrier->configure();
                        $shipping = $carrier->searchOptions($dimensions, $shipping_zipcode, $cart->total, $is_pickup);

                        $total_paid += $shipping['cost'];
                    } else {
                        return ['success' => false, 'msg' => __('checkout::cart.no_dimensions')];
                    }
                } else {
                    $shipping = [
                        'cost' => 0
                    ];
                }

                $transaction = $this->transactionsRepository
                    ->where('cart_id', $cart->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

                if($transaction == null) {
                    $transaction = $this->transactionsRepository->create([
                        'user_id' => $user->id,
                        'cart_id' => $cart->id,
                        'status' => 'pending',
                        'total_shipping' => $shipping['cost'],
                        'total_discounts' => $cart->discount,
                        'total_articles' => $cart->subtotal,
                        'total_paid' => $total_paid,
                        'shipping_address_id' => $request->input('shipping_address_id'), 
                        'afip_type_id' => $request->input('afip_type_id'),
                        'doc_number' => $request->input('doc_number'), 
                        'name_invoice' => $request->input('name_invoice'), 
                        'notes' => $request->input('note'),
                        'shipping_type' => $request->input('shipping_type'),
                        'shipping_address_extra' => $request->input('shipping_address_extra')
                    ]);
                } else {
                    $transaction->update([
                        'user_id' => $user->id,
                        'cart_id' => $cart->id,
                        'status' => 'pending',
                        'total_shipping' => $shipping['cost'],
                        'total_discounts' => $cart->discount,
                        'total_articles' => $cart->subtotal,
                        'total_paid' => $total_paid,
                        'shipping_address_id' => $request->input('shipping_address_id'), 
                        'afip_type_id' => $request->input('afip_type_id'), 
                        'doc_number' => $request->input('doc_number'), 
                        'name_invoice' => $request->input('name_invoice'), 
                        'note' => $request->input('note')
                    ]);

                    // elimina lineas de venta existentes en caso de que el carro haya sido actualizado
                    foreach($transaction->lines as $line) {
                        $line->delete();
                    }
                }

                foreach($cart->article_properties as $article_property) {
                    $line_price = $article_property->price;
                    if(is_null($article_property->price) && !config('config.SHOW_PRICING')) {
                        $line_price = 0;
                    }

                    $this->transactionLinesRepository->create([
                        'article_property_id' => $article_property->id,
                        'transaction_id' => $transaction->id,
                        'price' => $line_price,
                        'quantity' => $article_property->pivot->quantity,
                        'discount_amount' => $article_property->discount,
                        'discount_id' => !is_null($article_property->article->current_discount) ? 
                            $article_property->article->current_discount->id : null
                    ]);
                }

                if($request->input('shipping_address_id') != null) {
                    if(count($transaction->shippings) == 0) {
                        $this->shippingRepository->create([
                            'amount' => $shipping['cost'],
                            'status' => 'pending',
                            'shipping_method_id' => $request->input('shipping_method_id'),
                            'transaction_id' => $transaction->id
                        ]);
                    } else {
                        $foundedShipment = $this->shippingRepository
                                        ->where('status', 'pending')
                                        ->where('transaction_id', $transaction->id)
                                        ->first();

                        $foundedShipment->update([
                            'amount' => $shipping['cost'],
                            'shipping_method_id' => $request->input('shipping_method_id'),
                            'tracking_code' => null
                        ]);
                    }
                } else {
                    foreach ($transaction->shippings as $shipment) {
                        $shipment->delete();
                    }
                }

                // updated transaction with new lines and shipments
                $transaction = $this->transactionsRepository->find($transaction->id);

                DB::commit();

                $init_point = $gateway->process($cart, $transaction, $card);

                return ['success' => true, 'url' => $init_point];
            } else {
                abort(500);
            }
        } else {
            abort(404);
        }
    }

    public function confirmPayment(Request $request) {
        $gateway_id = $request->query('gateway_id');
        \Log::info('Recibe webhook ' . $gateway_id);

        if($request->input('type') != null && $request->input('type') == 'test') {
            http_response_code(200);
            return;
        }

        \Log::info('Payment Webhook query: ' . json_encode($request->query->all()));
        \Log::info('Payment Webhook request:' . json_encode($request->all()));

        if(isset($this->gateways[$gateway_id])) {
            $gateway = $this->gateways[$gateway_id];

            $cart_id = $request->query('cart_id');
            $transaction_id = $request->query('transaction_id');
            
            $cart = $this->cartRepository->find($cart_id);
            $transaction = $this->transactionsRepository->find($transaction_id);
            $payment_method = $this->paymentMethodsRepository->find($gateway_id);

            // si la transaccion fue aprobada es porque ya recibimos el pago
            if($transaction->status == 'approved') {
                \Log::info('Payment Webhook: transaccion ya aprobada');
                abort(500);
                return;
            }

            if($cart == null || $transaction == null || $payment_method == null) {
              \Log::info('Payment Webhook: Carrito, transaccion o metodo de pago no encontrado');
              abort(500);
              return;
            }

            $gateway->configure();
            $payment = $gateway->confirmPayment($request->query("type"), $request->input('data_id'));

            if($payment != null) {
                DB::beginTransaction();

                $transaction->update([
                    'status' => 'approved'
                ]);

                if($transaction->shipping_address_id != null) {
                    $this->shippingController->ship($transaction, $payment);
                }                            

                if($payment_method->is_offline) {
                    $this->paymentRepository->create([
                        'payment_method_id' => $payment_method->id,
                        'transaction_id' => $transaction->id,
                        'amount' => 0,
                        'status' => 'due'
                    ]);
                    $transaction->update([
                        'status' => 'waiting_for_admin'
                    ]);
                } else {
                    $this->transactionUtil->updateStock($transaction->id);

                    $this->paymentRepository->create([
                        'payment_method_id' => $payment_method->id,
                        'transaction_id' => $transaction->id,
                        'amount' => $transaction->total_paid,
                        'status' => 'paid'
                    ]);                    
                }

                DB::commit();

                if(!env('APP_DEBUG')){
                    $admins = User::whereHas('roles', function($query) {
                        $query->where('name', env('ROLE_TO_SEND_EMAIL'));
                    })->get();

                    foreach ($admins as $recipient) {            
                        $mail = Mail::to($recipient->email);

                        try {
                            $mail->send(new NewSellToAdmin($transaction));
                        } catch(\Exception $e) {
                            \Log::error($e);
                        }
                    }

                    if($transaction->user->email != null) {
                        $mail = Mail::to($transaction->user->email);
                    } else if($transaction->user->alternative_email != null) {
                        $mail = Mail::to($transaction->user->alternative_email);
                    }

                    try {
                        $mail->send(new NewSellToCustomer($transaction, $payment_method->extra_info));
                    } catch(\Exception $e) {
                        \Log::error($e);
                    }
                }

                http_response_code(200);
            } else {
                abort(500);
            }
        } else {
            abort(500);
        }
    }

    public function paymentProcessed(Request $request) {
        // Puede volver con status success definido internamente por nosotros o con estado approved definido por MercadoPago
        if($request->query('status') != null && ($request->query('status') == 'success' || $request->query('status') == 'approved')) {
            session()->forget('cart');
        }

        return view('checkout::payment-processed');
    }

    public function gatewayExtraInfo(Request $request, $payment_method_id) {
        if($request->ajax()) {
            $payment_method = $this->paymentMethodsRepository->find($payment_method_id);

            if($payment_method != null) {
                return $payment_method->extra_info;
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

    public function offlineReceipt(Request $request) {
        if(request()->query('barcode') != null) {
            $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
            $barcode = $generator->getBarcode(request()->query('barcode'), $generator::TYPE_CODE_128);

            return view('checkout::offline_receipt', compact('barcode'));
        } else {
            abort(404);
        }
    }
}
