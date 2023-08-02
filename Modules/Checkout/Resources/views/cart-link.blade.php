<link rel="stylesheet" href="{{ asset('transversal/css/cart-link.css') }}">
<div class="cart-position-strategic">
  <div class="shopping-cart-link" data-url="{{ route('checkout.shoppingcartlink') }}">
    <div class="shopping-cart-header">
      <i class="fa fa-shopping-cart cart-icon"></i>
      <span class="badge">
        @isset($cart)
          {{ $cart->quantity_total }}
        @else
          0
        @endif
      </span>
      @if(config('config.SHOW_PRICING'))
      <div class="shopping-cart-total">
        <span class="lighter-text">Total:</span>
        <span class="main-color-text">
          @isset($cart)
            {{ $cart->total_formatted }}
          @else
            $0,00
          @endisset
        </span>
      </div>
      @endif
    </div> <!--end shopping-cart-header -->

    <ul class="shopping-cart-items">
      @isset($cart)
        @if(count($cart->article_properties))
          @foreach($cart->article_properties as $article_property)
          <li class="clearfix">
            <div class="shopping-cart-items-img">
                <img src="{{ asset($article_property->article->thumb()) }}" 
              alt="{{ $article_property->article->getPrincipalPhoto()->name }}" />
            </div>
            <span class="item-name">{{ $article_property->article->title }}</span>
            @if(config('config.SHOW_PRICING'))
            <span class="item-price">{{ $article_property->getPriceFormatted() }}</span>
            @endif
            <span class="item-quantity">Cantidad: {{ $article_property->pivot->quantity }}</span>
          </li>
          @endforeach
        @else
          <li class="clearfix">
            No hay productos en el carrito
          </li>
        @endif
      @endisset
    </ul>

    <a href="{{route('front.checkout')}}" class="button">Checkout</a>
  </div> <!--end shopping-cart -->
</div>

@section('cart-link-javascripts')
<script type="text/javascript">
  $(function() {
 
    $("#cart").on("click", function() {      
      $(".shopping-cart-link").fadeToggle( "fast");
    });
  
  });
</script>
@endsection
