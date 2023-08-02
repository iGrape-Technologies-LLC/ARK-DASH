<form id="ajax-final-form" data-href="{{ route('checkout.processpaymentCheckout') }}" method="POST" class="login-form">
  @csrf        
  <div class="row">
    <div class="col-12">
      <div class="container payment-extra-info"></div>
    </div> 
  </div>
  
  <div class="row input-facturacion">
    <div class="col-12">
      <input type="checkbox" id="invoice-checkbox" name="invoice-info">Datos de facturacion
    </div>
  </div>

  <div id="invoice-data" style="display: none;">
    <div class="row">           
      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="control-label">Condicion frente a IVA</label> 
          <select name="afip_type_id" id="afip_type_id" class="selectize" required >
            <option value="">Seleccione</option>
            @foreach($afip_types as $type)
              <option value="{{ $type->id }}">
                {{ $type->name }}
              </option>
            @endforeach
          </select>             
        </div>
      </div>          
      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="control-label">CUIT/CUIL</label>
          <input class="form-control input-md" name="doc_number" 
            placeholder="Ingrese CUIT/CUIL" type="text" maxlength="255">
        </div>
      </div>      

      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="control-label">Nombre y apellido/Razon Social</label>
          <input class="form-control input-md" name="name_invoice" 
            placeholder="Ingrese Nombre o Razon Social" type="text" required maxlength="255">
        </div>
      </div>    

      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="control-label">Notas</label>
          <input class="form-control input-md" name="note" 
            placeholder="Notas" type="text">
        </div>
      </div>    

      <input type="hidden" name="shipping_address_type" id="shipping_address_type">
      <input type="hidden" name="shipping_address_id" id="shipping_address_id">
      <input type="hidden" name="gateway_id" id="gateway_id">
      <input type="hidden" name="shipping_method_id" id="shipping_method_id">
      <input type="hidden" name="shipping_type" id="shipping_type" value="address">
      <input type="hidden" name="shipping_address_extra" id="shipping_address_extra">
      <input type="hidden" name="shipping_extra_zip" id="shipping_extra_zip">

      {{--<div class="col-12 text-center">
        <button class="btn btn-common" type="submit">Aceptar</button>
      </div>--}}
    </div>
  </div>
</form>

<div class="alert alert-success" id="profile_message" style="display: none;">Perfil actualizado con exito</div>
<div class="alert alert-danger" id="profile_message_error"  style="display: none;">
  Ha ocurrido un error. Por favor, intente luego nuevamente.
</div>
