<form id="ajax-address-form" data-href="{{ route('front.address') }}" method="POST" class="login-form validate">
        @csrf        
        <div class="row">     
          <div class="col-md-12">
            <div class="form-group mb-3">
              <label class="control-label">Nombre</label>
              <input class="input-text input-md" name="name"  type="text" required maxlength="255">
            </div>
          </div> 

          <div class="col-md-12">
            <div class="form-group mb-3">
              <label class="control-label">Dirección</label>
              <input class="input-text input-md" name="street" type="text" required maxlength="255">
            </div>
          </div>     

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Número</label>
              <input class="input-text input-md" name="street_number"  type="number" required maxlength="255">
            </div>
          </div>   

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Piso/Depto</label>
              <input class="input-text input-md" name="floor"  type="text" maxlength="255">
            </div>
          </div>             

          <div class="col-md-4" style="display: none;">
            <div class="form-group mb-3">
              <label class="control-label">Depto</label>
              <input class="input-text input-md" name="apartment"  type="text" maxlength="255">
            </div>
          </div>    

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Ciudad</label> 
              <input class="input-text input-md" name="city"  type="text" maxlength="255">
              {{--<select name="city_id" class="selectize" required >
                <option value="">Seleccione ciudad</option>
                @foreach($cities as $city)
                  <option value="{{ $city->id }}"
                      @if(auth()->user() != null && $city->id == auth()->user()->city_id)
                        selected
                      @endif
                    >
                    {{ $city->name }}
                  </option>
                @endforeach
              </select> --}}            
            </div>
          </div>    

          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Código postal</label>
              <input class="input-text input-md" name="postal_code"  type="text" required maxlength="255">
            </div>
          </div>        

          <div class="col-12 text-center">
            <input value="Aceptar" class="btn custom-button aceptarcerrar" type="submit">
            <button type="button" class="btn btn-secondary aceptarcerrar" data-dismiss="modal" style="margin-top: 5px;">Cerrar</button>
          </div>
        </div>

      </form>

  <div class="alert alert-success" id="address_message" style="display: none;">Dirección creada con exito</div>
  <div class="alert alert-danger" id="address_message_error"  style="display: none;">Ha ocurrido un error. Por favor, intente luego nuevamente.</div>