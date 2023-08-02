<link rel="stylesheet" href="{{ asset('transversal/css/cart.css') }}">

<div class="row cart-tr">
  <div class="col-md-8">    
    <div class="card">

      <div class="table-responsive">

      <table class="table table-borderless table-shopping-cart" id="table-shopping-cart" data-url="{{ route('checkout.cart.list') }}">
      <thead class="text-muted">
      <tr class="small text-uppercase">
        <th scope="col">Producto</th>
        <th scope="col" width="120">Cantidad</th>
        @if(config('config.SHOW_PRICING'))
        <th scope="col" width="120">Precio</th>
        @endif
        <th scope="col" class="text-right d-none d-md-block" width="200"> </th>
      </tr>
      </thead>
      <tbody id="cartListing">        
      </tbody>
      </table>

      </div> <!-- table-responsive.// -->

     

      </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
          @if(config('config.SHOW_PRICING'))
          <dl class="dlist-align">
            <dt>Subtotal:</dt>
            <dd class="text-right cart-subtotal">$0,00</dd>
          </dl>
          <dl class="dlist-align">
            <dt>Envío:</dt>
            <dd class="text-right cart-shipping-cost" data-cost="0">+ $0,00</dd>
          </dl>
          <dl class="dlist-align">
            <dt>Descuentos:</dt>
            <dd class="text-right text-danger cart-discount">- $0,00</dd>
          </dl>
          
          <dl class="dlist-align">
            <dt>Total:</dt>
            <dd class="text-right text-dark b"><strong class="cart-total">$0,00</strong></dd>
          </dl>
          <hr>        
          @endif  
          <a href="{{route('front.checkout')}}" class="btn btn-primary btn-block"> Finalizar compra </a>
          <a href="{{route('front.articleslist')}}" class="btn btn-light btn-block">Continuar comprando</a>
      </div> <!-- card-body.// -->
    </div>
  </div>
</div>

@section('javascript')

<script type="text/javascript">

  $(function() {
      updateCartView();

      updateCartTotals();
  });

  function updatelisteners(){
    $('#table-shopping-cart .delete-btn').on('click', function(e) {
      var url = $(this).data('url');
      $(this).parent().parent().remove();

      $('.cart-final-total').html('Calculando..');

      removeCartItem(url, function() {
        $('div.modal#cart-slide-modal').modal('hide');
        updateCartTotals();
        actualizarCarts();
      });
    });     

    $('#table-shopping-cart .quantity-article').on('focusin', function(){
        $(this).data('val', $(this).val());
    });

    $("#table-shopping-cart .quantity-article").on('change', function(e){  
        var prevQuantity = $(this).data('val');     
        var quantity = $(this).val();
        var quantity_data_url = $(this).data('url');
        var article_price = $(this).data('price');
        var article_property_id = $(this).data('id');

        var article_total = quantity * article_price;
        $('div.total-price#total-' + article_property_id).html(formatMoney(article_total));

        $('.cart-final-total').html('Calculando..');

        input = $(this);

        setItemQuantity(quantity_data_url, quantity, function() {
          updateCartTotals();
          actualizarCarts();
        }, function(){
          updateCartTotals();
          actualizarCarts();
          input.val(prevQuantity);
        });
    });
  }

  function updateCartTotals() {
    getCartTotals(function(err, totals) {
      if(!err) {
        $('dd.cart-subtotal').html(formatMoney(totals.subtotal));
        $('dd.cart-discount').html('- ' + formatMoney(totals.discount));
        $('strong.cart-total').html(formatMoney(totals.total));
      } else {
        console.log(err);
      }
    });
  }

  function updateCartView(){
      var update_url = $('#table-shopping-cart').data('url');

      $.ajax({
        url: update_url,
        type: 'GET',
        dataType: 'html',
        timeout: 20000,
        success: function(res) {     
          $('#cartListing').html(res);
          updatelisteners();
        },
        error: function(err) {
          console.log(err)
        }
      });
    }
</script>
@endsection