<?php

namespace App\Http\Middleware;

use Closure;

class CustomerUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $expressRoutes = [
            route('front.checkout'),
            route('front.profile.checkout'),
            route('front.address.listCheckout')
        ];

        if (auth()->user() != null || (in_array(url()->current(), $expressRoutes) && config('config.EXPRESS_CHECKOUT'))){
            return $next($request);
        } else {
            return redirect()->route('front.login');
        }
    }
}
