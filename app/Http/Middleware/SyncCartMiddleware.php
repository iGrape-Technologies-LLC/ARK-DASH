<?php

namespace App\Http\Middleware;

use Closure;
use Modules\Checkout\Entities\Cart;

class SyncCartMiddleware
{
    private $cartRepository;

    public function __construct(Cart $cartRepo) {
        $this->cartRepository = $cartRepo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if($user != null) {
            $cart = $this->cartRepository->userCurrentCart($user);
            if($cart != null) {
                $changed = $cart->syncArticlesByStockAndAvailability();
                if($changed) {
                    $request->session()->flash('notificationmsg', __('checkout::cart.changed'));
                }
                $request->session()->put('cart', $cart);
            }
        }

        return $next($request);
    }
}
