<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('checkout')->group(function() {
    Route::post('/add-item', 'CartController@addCartItem')
    	->name('checkout.addcartitem');

    Route::get('/remove-item/{article_property_id}', 'CartController@removeCartItem')
    	->name('checkout.removecartitem');

    Route::get('/increse-quantity/{article_property_id}', 'CartController@increaseItemQuantity')
        ->name('checkout.increaseitemquantity');

    Route::get('/decrease-quantity/{article_property_id}', 'CartController@decreaseItemQuantity')
        ->name('checkout.decreaseitemquantity');

    Route::post('/set-quantity/{article_property_id}', 'CartController@setItemQuantity')
        ->name('checkout.setitemquantity');

    Route::get('/current-cart-data', 'CartController@getCart')
        ->name('checkout.getcartdata');

    Route::get('/cart-totals', "CartController@getTotals")
        ->name('checkout.carttotals');

    Route::get('/shopping-cart-slide', 'CartController@shoppingCartSlide')
    	->name('checkout.shoppingcartslide');

    Route::get('/shopping-cart-link', 'CartController@shoppingCartLink')
        ->name('checkout.shoppingcartlink');

    Route::get('/carro/lista', 'CartController@cartList')
        ->name('checkout.cart.list');

    Route::get('/process/{gateway_id}', 'CheckoutController@processPayment')
    	->name('checkout.processpayment');

    Route::post('/process', 'CheckoutController@processPayment')
        ->name('checkout.processpaymentCheckout');

    Route::post('/confirm', 'CheckoutController@confirmPayment')
        ->name('checkout.confirmPayment');

    Route::get('/processed', 'CheckoutController@paymentProcessed')
        ->name('checkout.paymentprocessed');

    Route::get('/extra-info/{payment_method_id}', 'CheckoutController@gatewayExtraInfo')
        ->where('payment_method_id', '^[0-9]+$')->name('checkout.gatewayextrainfo');

    Route::get('/offline-receipt', 'CheckoutController@offlineReceipt')
        ->name('checkout.offlinereceipt');
});
