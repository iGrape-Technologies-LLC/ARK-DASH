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

Route::prefix('shipping')->group(function() {
    Route::post('/search-options', 'ShippingController@searchOptions')
    	->name('shipping.searchoptions');

    Route::post('/carrier-payment-methods', 'ShippingController@carrierAvailablePaymentMethods')
    	->name('shipping.carrierpaymentmethods');

    Route::get('/carrier-locations', 'ShippingController@carrierLocations')
    	->name('shipping.carrierlocations');

    Route::get('/ticket/{id}', 'ShippingController@getTicket')
    	->name('shipping.ticket');
});
