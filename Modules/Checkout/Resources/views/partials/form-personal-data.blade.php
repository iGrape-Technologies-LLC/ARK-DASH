<form id="ajax-profile-form" data-href="{{ route('front.profile.checkout') }}" method="POST" class="login-form validate">
        @csrf        
        <div class="row"> 
          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Nombre</label>
              <input class="form-control input-md @error('name') is-invalid @enderror" name="name" 
                placeholder="Ingrese nombre" type="text" required maxlength="255" 
                value="{{ auth()->user() != null ? auth()->user()->name : '' }}">

              @error('name')
                <span class="invalid-feedback" role="alert">
                    {{ $message }}
                </span>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Apellido</label>
              <input class="form-control input-md @error('lastname') is-invalid @enderror" name="lastname" 
                placeholder="Ingrese nombre" type="text" required maxlength="255" 
                value="{{ auth()->user() != null ? auth()->user()->lastname : '' }}">
              @error('lastname')
                <span class="invalid-feedback" role="alert">
                    {{ $message }}
                </span>
              @enderror
            </div>
          </div>
                  
          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Teléfono</label>
              <input class="form-control input-md @error('phone') is-invalid @enderror" required="required"
              name="phone" placeholder="Ej. +54 9 341 2712127"  type="text" maxlength="255" 
              value="{{ auth()->user() != null ? auth()->user()->phone : '' }}" >
              @error('phone')
                <span class="invalid-feedback" role="alert">
                    {{ $message }}
                </span>
              @enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">DNI</label>
              <input class="form-control input-md @error('doc_number') is-invalid @enderror" name="doc_number" 
                placeholder="Ingrese DNI" type="text" maxlength="255" required="required"
                value="{{ auth()->user() != null ? auth()->user()->doc_number : '' }}">

              @error('name')
                <span class="invalid-feedback" role="alert">
                    {{ $message }}
                </span>
              @enderror
            </div>
          </div>
          @if(config('config.EXPRESS_CHECKOUT'))
          <div class="col-md-6">
            <div class="form-group mb-3">
              <label class="control-label">Email</label>
              <input type="email" name="alternative_email" maxlength="255" required placeholder="Ingrese email"
                value="{{ auth()->user() != null ? auth()->user()->alternative_email : '' }}" 
                class="form-control input-md @error('alternative_email') is-invalid @enderror">
            </div>
          </div>
          @endif

          <div class="col-12 text-center">
            <button class="btn btn-common" type="submit">Aceptar</button>
          </div>
        </div>

</form>

<div class="alert alert-success" id="profile_message" style="display: none;">Perfil actualizado con exito</div>
<div class="alert alert-danger" id="profile_message_error"  style="display: none;">Ha ocurrido un error. Por favor, intente luego nuevamente.</div>
