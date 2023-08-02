<?php

# Front Page
	Route::get('/', 'HomeController@home')->name('admin.home');

	Auth::routes(['verify' => env('EMAIL_VERIFICATION', false), 'reset' => false]);


	// Registration Routes...
	Route::match(['get'], 'register', function () {
        return view('errors.403');
    })->name('register');

	Route::middleware(['auth'])->group(function() {
		Route::prefix('admin')->middleware('staff')->group(function() {
			Route::get('/home', 'HomeController@indexAdmin')->name('admin.home');
			Route::post('/home', 'HomeController@import');
		});
	});


	Route::middleware(['auth'])->group(function() {
		Route::get('logout', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);
	});
