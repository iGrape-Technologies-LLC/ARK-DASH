<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

Route::post('/login', 'Auth\LoginController@loginApi');

Route::middleware('auth.basic')->group(function() {
    Route::get('/articles', "ArticlesController@index");
});
