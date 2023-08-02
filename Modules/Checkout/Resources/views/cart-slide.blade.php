<link rel="stylesheet" href="{{ asset('transversal/css/cart-slide.css') }}">

{{--<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cart-slide-modal">
    Boton para probar Cart Side
</button>--}}

<div class="modal fade" id="cart-slide-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2" aria-hidden="true" data-url="{{ route('checkout.shoppingcartslide') }}">
  <div class="modal-dialog modal-dialog-slideout" role="document">
    <div class="modal-content modal-content-cart">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Carrito de compras</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="shopping-cart">
          @isset($cart)
            @if(count($cart->article_properties) > 0)
            @foreach($cart->article_properties as $article_property)
            <div class="item">
              <div class="buttons">
                <span class="delete-btn" data-url="{{ route('checkout.removecartitem', $article_property->id) }}"></span>
                @if(auth()->user() == null)        
                  <a class="like-btn" href="{{route('front.login')}}"></a>
                @else
                <a href="#!" class="like-btn add_favorite_item @if($article_property->article->is_user_favorite) favorited @endif  " data-value="{{ $article_property->article->id }}" 
                data-url="{{ route('front.togglefavoriteAjax') }}"></a> 
                @endif 

                {{--@if($article_property->article->is_user_favorite)
                <a href="#" class="like-btn remove_favorite_item favorited" data-value="{{ $article_property->article->id }}" 
                data-url="{{ route('front.deletefavoriteAjax') }}"></a>
                @else
                <a href="#" class="like-btn add_favorite_item" data-value="{{ $article_property->article->id }}" 
                data-url="{{ route('front.addfavoriteAjax') }}"></a>
                @endif--}}
              </div>
           
              <div class="image">
                <img src="{{ asset($article_property->article->thumb()) }}" 
                  alt="{{ $article_property->article->getPrincipalPhoto()->name }}" />
              </div>
              
              <div class="text-cart">
                  <div class="description">
                    <span>{{ $article_property->article->title }}</span>
                  </div>
               
                  <div class="quantity">                              
                    <button class="minus-btn" type="button" name="button" id="decrease-quantity" data-id="{{ $article_property->id }}"
                      data-url="{{ route('checkout.decreaseitemquantity', $article_property->id) }}" 
                      data-price="{{ $article_property->final_price }}">
                        <img src="{{asset('transversal/img/minus.svg')}}" alt="incrementar cantidad" />
                    </button>
                    <input type="text" name="name" value="{{ $article_property->pivot->quantity }}" data-id="{{ $article_property->id }}"
                    id="current-quantity-{{ $article_property->id }}" data-url="{{route('checkout.setitemquantity', $article_property->id) }}"   
                    class="quantity-article" data-price="{{ $article_property->final_price }}">
                    <button class="plus-btn" type="button" name="button" id="increase-quantity" data-id="{{ $article_property->id }}"
                    data-url="{{ route('checkout.increaseitemquantity', $article_property->id) }}" 
                    data-price="{{ $article_property->final_price }}">
                      <img src="{{asset('transversal/img/plus.svg')}}" alt="reducir cantidad" />
                    </button>
                  </div>
                  @if(config('config.SHOW_PRICING'))
                  <div class="total-price" id="total-{{ $article_property->id }}">{{ $article_property->pivot->line_total_formatted }}</div>
                  @endif 
              </div>
            </div>
            @endforeach
            @else
              <div class="item">
                <p>No hay productos en el carrito</p>
              </div>
            @endif
          @endisset
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <a href="{{route('front.checkout')}}" class="btn btn-success float-right">Finalizar compra</a>
      </div>
    </div>
  </div>
</div>

@section('cart-slide-javascripts')

<script type="text/javascript">
  listenerFavorites();
  $(function() {




     
      $('.minus-btn').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $input = $this.closest('div').find('input');
        var value = parseInt($input.val());
     
        if (value && 1) {
            value = value - 1;
        } else {
            value = 0;
        }
     
      $input.val(value);
     
    });
     
    $('.plus-btn').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        var $input = $this.closest('div').find('input');
        var value = parseInt($input.val());
     
        if (value && 100) {
            value = value + 1;
        } else {
            value =100;
        }
     
        $input.val(value);
    });



  });
</script>
@endsection