@if(auth()->user() != null)
@foreach(auth()->user()->addresses as $address)
<div class="col-special-ship-address">
  <div class="card">
    <div class="card-header">
      <h5 class="my-0">{{ $address->name }}</h5>
    </div>
    <div class="card-body">          
      <ul class="list-unstyled mb-4">
        <li class="cut-text-1"><span class="span-addresses-tr">Direccion</span>: {{ $address->street }} {{ $address->street_number }}</li>
        <li class="cut-text-1"><span class="span-addresses-tr">Provincia</span>: {{ $address->city->state->name }}</li>
        <li class="cut-text-1"><span class="span-addresses-tr">Codigo postal</span>: {{ $address->postal_code }}</li>
        <li class="cut-text-1"><span class="span-addresses-tr">Pais</span>: {{ $address->city->state->country->name }}</li>
      </ul>

      <button class="btn btn-block btn-outline-primary addresses" data-id="{{ $address->id }}" data-url="{{ route('shipping.searchoptions') }}">
        Calcular envío
      </button>
    </div>
  </div>
</div>
@endforeach
@endif
  
<script type="text/javascript">
  $(".addresses").click(function(){
    
     $(".addresses").each(function() {
          $(this).blur();
          $(this).removeClass('btn-primary');
          $(this).addClass('btn-outline-primary ');
      });
  });
</script>