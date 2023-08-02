<?php

namespace Modules\Checkout\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Checkout\Entities\Cart;
use App\Models\ArticleProperty;

class CartController extends Controller {

	private $cartRepository;

	public function __construct(Cart $cartRepository) {
		$this->cartRepository = $cartRepository;
	}

	public function addCartItem(Request $request) {
        $request->validate([
            'article_property_id' => ['required', 'numeric', 'exists:article_properties,id']
        ]);

        $quantityToAdd = 1;
        if(($request->input('quantity'))){
            $quantityToAdd = $request->input('quantity');
        }

        $article_property_id = $request->input('article_property_id');

        $user = auth()->user();

        // buscar un carrito que todavia no se haya confirmado como venta
        $cart = $this->cartRepository->userCurrentCart($user);

        // si el usuario todavia no tiene carrito lo crea
        if($cart == null) {
            if($user == null) {
                $user_id = null;
                $session_id = session()->getId();
            } else {
                $user_id = $user->id;
                $session_id = null;
            }

            $cart = $this->cartRepository->create([
                'user_id' => $user_id,
                'session_id' => $session_id
            ]);
        }

        $art_prop = ArticleProperty::findorfail($article_property_id);

        // si el articulo no esta en el carrito agregarlo, sino sumarle cantidad
        if(!in_array($article_property_id, $cart->article_properties()->pluck('article_property_id')->toArray())) {
            if(!$art_prop->articleHasStock($quantityToAdd))
                return ['success' => false, 'msg'=> __('general.not_have_stock')];
            else
                $cart->article_properties()->attach([$article_property_id => ['quantity' => $quantityToAdd]]);
        } else {
            $quantity = 1;
            foreach($cart->article_properties as $article_property) {
                if($article_property->pivot->article_property_id == $article_property_id) {
                    $quantity = $article_property->pivot->quantity;
                    break;
                }
            }

            $quantity += $quantityToAdd;
            if(!$art_prop->articleHasStock($quantity))
                return ['success' => false, 'msg'=> __('general.not_have_stock')];
            else
                $cart->article_properties()->updateExistingPivot($article_property_id, ['quantity' => $quantity]);
        }

        return ['success' => true];
    }

    public function removeCartItem(Request $request, $article_property_id) {
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);

        if($cart != null) {
            $cart->article_properties()->detach($article_property_id);

            return ['success' => true];
        } else {
            return ['success' => false, 'msg' => 'Cart not found'];
        }
    }

    public function setItemQuantity(Request $request, $article_property_id) {
        $user = auth()->user();

        $quantity = $request->input('quantity');

        $cart = $this->cartRepository->userCurrentCart($user);

        if($cart != null) {
            foreach($cart->article_properties as $article_property) {
                if($article_property->id == $article_property_id) {
                    if($quantity == 0) {
                        $article_property->pivot->delete();
                        break;
                    }
                    if($article_property->articleHasStock($quantity)){
                        $cart->article_properties()->updateExistingPivot($article_property_id, [
                                'quantity' => ($quantity)
                            ]);
                    } else{
                        return ['success' => false, 'msg'=>  __('general.not_have_stock')];
                    }
                    break;
                }
            }
            return ['success' => true];
        } else {
            return ['success' => false, 'msg' => 'Cart not found'];
        }
    }

    private function changeItemQuantity($article_property_id, $increase = true) {
    	$user = auth()->user();

		$cart = $this->cartRepository->userCurrentCart($user);

        if($cart != null) {
            foreach($cart->article_properties as $article_property) {
            	if($article_property->id == $article_property_id) {
            		if($increase) {
                        if($article_property->articleHasStock($article_property->pivot->quantity + 1)){
                			$cart->article_properties()->updateExistingPivot($article_property_id, [
    	            			'quantity' => ($article_property->pivot->quantity + 1)
    	            		]);
                        } else{
                            return ['success' => false, 'msg'=>  __('general.not_have_stock')];
                        }
            		} else {
                        $quantity = $article_property->pivot->quantity - 1;
                        if($quantity == 0) {
                            $article_property->pivot->delete();
                            break;
                        }
        				$cart->article_properties()->updateExistingPivot($article_property_id, [
	            			'quantity' => $quantity
	            		]);
            		}
            		break;
            	}
            }

            return ['success' => true];
        } else {
            return ['success' => false, 'msg' => 'Cart not found'];
        }
    }

    public function increaseItemQuantity($article_property_id) {
    	return $this->changeItemQuantity($article_property_id, true);
    }

    public function decreaseItemQuantity($article_property_id) {
    	return $this->changeItemQuantity($article_property_id, false);
    }

    public function shoppingCartSlide() {
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);
        request()->session()->put('cart', $cart);

        return view('checkout::cart-slide', compact('cart'));
    }

    public function shoppingCartLink() {
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);
        request()->session()->put('cart', $cart);

        return view('checkout::cart-link', compact('cart'));
    }

    public function cartList(){
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);
        request()->session()->put('cart', $cart);

        return view('checkout::partials.cart-list', compact('cart'));
    }

    public function getCart() {
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);
        request()->session()->put('cart', $cart);

        return json_encode($cart);
    }

    public function getTotals() {
        $user = auth()->user();

        $cart = $this->cartRepository->userCurrentCart($user);
        if($cart != null) {
            request()->session()->put('cart', $cart);

            return [
                'subtotal' => $cart->subtotal,
                'discount' => $cart->discount,
                'total' => $cart->total
            ];
        } else {
            abort(404);
        }
    }
}
