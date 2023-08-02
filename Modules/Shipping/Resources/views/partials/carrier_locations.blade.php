@if(count($locations) > 0)
@foreach($locations as $location)
<div class="col-special-ship-address">
  <div class="card">
    <div class="card-header">
      <h5 class="my-0">{{ $location['name'] }}</h5>
    </div>
    <div class="card-body">          
      <ul class="list-unstyled mb-4">
        <li><span class="span-addresses-tr">Direccion</span>: {{ $location['street'] . ' ' . $location['street_number'] }}</li>
        {{--<li><span class="span-addresses-tr">Provincia</span>: {{ $location['state'] }}</li>--}}
        <li><span class="span-addresses-tr">Codigo postal</span>: {{ $location['zip'] }}</li>
        <li><span class="span-addresses-tr">Pais</span>: {{ $location['country'] }}</li>
        <li><span class="span-addresses-tr">Precio</span>: {{ $location['cost_formatted'] }}</li>
      </ul>
      <button class="btn btn-block btn-outline-primary custom-button carrier-location" data-address="{{ $location['street'] . ', ' .  
      $location['zip'] . ', ' . $location['city'] . ', ' . $location['country'] }}" data-carrier="{{ $carrier_id }}"
      data-cost="{{ $location['cost'] }}" data-zipcode="{{ $location['zip'] }}" data-addressid="{{ $location['address_id'] }}">
        Seleccionar
      </button>
    </div>
  </div>
</div>
@endforeach
@else
<p>No se han encontrado sucursales con el código postal ingresado</p>
@endif
