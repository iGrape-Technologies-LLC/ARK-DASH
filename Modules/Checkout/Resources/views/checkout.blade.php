<link rel="stylesheet" href="{{ asset('transversal/css/checkout.css') }}">

<div class="row checkout-tr" id="checkout-tr">
	<div class="col-xs-12 col-md-8">
		@include('checkout::partials.form-checkout')
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="card shadow mb-3">
			<div class="card-body">			
				<div class="">
					<label>Tienes un cupon?</label>
					<div class="input-group">
						<input type="text" class="form-control" name="" placeholder="Cupon">
						<span class="input-group-append">
							<button class="btn btn-primary">Aplicar</button>
						</span>
					</div>
				</div>			
			</div> <!-- card-body.// -->
		</div>
		<div class="card shadow">
			<div class="card-body">
					@if(config('config.SHOW_PRICING'))
					<dl class="dlist-align">
					  <dt>Subtotal:</dt>
					  @if(session()->has('cart'))
					  <dd class="text-right cart-articles-total" data-amount="{{ session()->get('cart')->subtotal }}">
					  	{{ session()->get('cart')->subtotal_formatted }}
					  </dd>
					  @else
					  <dd class="text-right cart-articles-total" data-amount="0">$0,00</dd>
					  @endif
					</dl>
					<dl class="dlist-align">
					  <dt>Envío:</dt>
					  <dd class="text-right cart-shipping-total" data-amount="0">+ $0,00</dd>
					</dl>
					<dl class="dlist-align">
					  <dt>Descuentos:</dt>
					  @if(session()->has('cart'))
					  <dd class="text-right text-danger cart-discount" data-amount="{{ session()->get('cart')->discount }}">
					  	- {{ session()->get('cart')->discount_formatted }}
					  </dd>
					  @else
					  <dd class="text-right text-danger">- $0,00</dd>
					  @endif
					</dl>
					
					<dl class="dlist-align">
					  <dt>Total:</dt>
					  @if(session()->has('cart'))
					  <dd class="text-right text-dark b">
					  	<strong class="cart-total" data-amount="{{ session()->get('cart')->total }}">
					  		{{ session()->get('cart')->total_formatted }}
					  	</strong>
					  </dd>
					  @else
					  	<dd class="text-right text-dark b">
					  	<strong class="cart-total" data-amount="0">
					  		$0,00
					  	</strong>
					  </dd>
					  @endif
					</dl>
					<hr>	
					@endif			
					<div class="alert alert-success" id="form_message" style="display: none;">Redireccionando...</div>
					<div class="alert alert-danger" id="form_message_error"  style="display: none;">Ha ocurrido un error. Por favor, intente luego nuevamente.</div>
	
					<a href="#" class="btn btn-primary btn-block disabled" id="end-checkout" > Finalizar compra </a>
					{{--<a href="{{ route('front.articleslist') }}" class="btn btn-light btn-block">Continuar comprando</a>--}}
			</div> <!-- card-body.// -->
		</div>
	</div>

	<input type="hidden" id="cart_id" value="{{ session()->has('cart') ? session()->get('cart')->id : '' }}">
</div>

@section('javascript')

<script src="{{ URL::asset('transversal/js/transversal.js?v=' . $asset_v) }}"></script>
<script src="{{ URL::asset('transversal/js/checkout-personal-data.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
  var addressesUrl = "{{ route('front.address.listCheckout') }}";
</script>
<script src="{{ URL::asset('transversal/js/checkout-address-form.js?v=' . $asset_v) }}"></script>
@endsection
