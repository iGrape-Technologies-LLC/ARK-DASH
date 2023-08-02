<link href="{{asset('transversal/plugins/wizard/css/gsi-step-indicator.css')}}" rel="stylesheet" />
<link href="{{asset('transversal/plugins/wizard/css/tsf-step-form-wizard.css')}}" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="">
<div class="tsf-wizard tsf-wizard-1">
        <!-- BEGIN NAV STEP-->
        <div class="tsf-nav-step">
          <!-- BEGIN STEP INDICATOR-->
          <ul class="gsi-step-indicator triangle gsi-style-1  gsi-transition ">
            <li class="current" data-target="step-first">
              <a href="#0">
                  <span class="number">1</span>
                  <span class="desc">
                      <label>Mis datos</label>
                      <span>Completa con tus datos</span>
                  </span>
              </a>
            </li>
            <li data-target="step-2">
              <a href="#0">
                  <span class="number">2</span>
                  <span class="desc">
                      <label>Envios</label>
                      <span>Como te lo enviamos?</span>
                  </span>
              </a>
            </li>
            <li data-target="step-3">
              <a href="#0">
                  <span class="number">
                      3
                  </span>
                  <span class="desc">
                      <label>Pago</label>
                      <span>Como lo pagas?</span>
                  </span>
              </a>
            </li>
          </ul>
          <!-- END STEP INDICATOR--->
      </div>
        <!-- END NAV STEP-->
        <!-- BEGIN STEP CONTAINER -->

        <div class="tsf-container shadow">
          <!-- BEGIN CONTENT-->
          <div class="tsf-content" id="checkout-form">
            <!--<form class="tsf-form">-->
            <!-- BEGIN STEP 1-->
            <div class="tsf-step step-first active">
              <fieldset>                
                  <legend>Primero, tus datos</legend>
                  <!-- BEGIN STEP CONTENT-->
                  <div class="tsf-step-content">                    
                    @include('checkout::partials.form-personal-data')
                  </div>
                  <!-- END STEP CONTENT-->               
              </fieldset>
            </div>
            <!-- END STEP 1-->
            <!-- BEGIN STEP 2-->
            <div class="tsf-step step-2 shipping-tr">
              <fieldset>
                <legend>Como te lo enviamos?</legend>
                <!-- BEGIN STEP CONTENT-->
                <div class="tsf-step-content">
                    <ul class="nav nav-pills">
                      @if(count($shipping_methods))
                      @if(config('config.SHOW_PRICING'))
                        <li class="nav-item pill-1">
                            <a class="nav-link addresses-type" data-id="0" data-toggle="tab" href="#sh-address" 
                            onclick="shippingTypeSelected('address')">Mi domicilio</a>
                        </li>
                        @foreach($shipping_methods as $shipping_method)
                        @if($shipping_method->has_pick_up)
                        <li class="nav-item pill-2">
                            <a class="nav-link addresses-type" data-id="1" data-toggle="tab" 
                              href="#sh-{{ str_replace(' ', '_', strtolower($shipping_method->name)) }}"
                              onclick="shippingTypeSelected('pick_up_carrier')">
                              Sucursal {{ $shipping_method->name }}
                            </a>
                        </li>
                        @endif
                        @endforeach
                      @endif
                      @endif

                      <li class="nav-item pill-3">
                          <a class="nav-link addresses-type" data-id="2" data-toggle="tab" href="#sh-local"
                          onclick="shippingTypeSelected('pick_up_store')">Retiro en local</a>
                      </li>
                    </ul>
                    <div class="tab-contante">
                      @if(config('config.SHOW_PRICING'))
                      <div class="tab-pane fade panel-tr-shipping" id="sh-address" role="tabpanel">
                        <div class="row">
                          
                          <div class="col-md-8">                              
                                <div class="row addresses-tr">
                                    <div class="addresses-tr shiping-addresses-tr">
                                      <div class="intermediate-width-full" id="addresses_list"></div>
                                    </div>
                                  </div>
                                <div class="row">
                                  @if(auth()->user() != null && count(auth()->user()->addresses)>0)
                                    <a href="#!" class="mt-30 mt-no-mobile" data-toggle="modal" data-target="#newAddressModal">
                                      @lang('shipping::general.or_add_address')
                                    </a>
                                  @else
                                    <a href="#!" class="mt-30 mt-no-mobile" data-toggle="modal" data-target="#newAddressModal">
                                      @lang('shipping::general.add_address')
                                    </a>
                                  @endif

                                  <div class="modal fade" id="newAddressModal" tabindex="-1" role="dialog" aria-labelledby="newAddressModalLabel" 
                                  aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="exampleModalLabel">@lang('shipping::general.add_address')</h5>
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                          </button>
                                        </div>
                                        <div class="modal-body">
                                          @include('shipping::partials.form-address')
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                          </div>                       
                          <div class="col-md-4">
                            <span id="shipping-methods-data" data-json='@json($shipping_methods)'></span>
                            <div class="row">
                              Enviar con:
                            </div>
                            @foreach($shipping_methods as $shipping_method)
                              <div class="row">
                                <button class="btn btn-block btn-outline-primary shipping-methods-selector disabled"  
                                  data-id="{{ $shipping_method->id }}" disabled
                                  data-shipping-payment-url="{{ route('shipping.carrierpaymentmethods') }}">
                                  {{ $shipping_method->name }}
                                </button>
                              </div>
                              <div class="row shipping-costs" id="shipping-cost-{{ $shipping_method->id }}" data-cost=""></div>
                            @endforeach
                          </div>
                          <div class="col-md-12 ">
                              
                          </div>
                        </div>
                      </div>
                      @foreach($shipping_methods as $shipping_method)
                      @if($shipping_method->has_pick_up)
                      <div class="tab-pane fade panel-tr-shipping" id="sh-{{ str_replace(' ', '_', strtolower($shipping_method->name)) }}" 
                        role="tabpanel">
                          <div class="row">
                            <div class="col-md-5">
                              <div class="card shadow mb-3">
                                <div class="card-body">                                
                                  <div class="">
                                    <label>Ingrese su codigo postal</label>
                                    <div class="input-group">
                                      <input type="text" class="form-control" name="" placeholder="Codigo postal" id="locations-search-zipcode">
                                      <span class="input-group-append">
                                        <button class="btn btn-primary" id="search-locations" data-url="{{ route('shipping.carrierlocations') }}" 
                                        data-id="{{ $shipping_method->id }}" data-amount="{{ session()->get('cart')->total }}"
                                        data-error="@lang('checkout::process.enter_zipcode_error')">
                                          Buscar
                                        </button>
                                      </span>
                                    </div>
                                  </div>
                                
                                </div> <!-- card-body.// -->
                              </div>
                            </div>
                            <div class="col-md-7">
                              <div class="addresses-tr shiping-addresses-tr">
                                <div class="intermediate-width-full carrier-locations"></div>
                              </div>
                            </div>
                          </div>
                      </div>
                      @endif
                      @endforeach
                      @endif
                      <div class="tab-pane fade panel-tr-shipping" id="sh-local" role="tabpanel">
                        <div class="row addresses-tr">
                          <div class="col-md-12">
                            <div class="addresses-tr shiping-addresses-tr">
                                <div class="intermediate-width-full store-locations">
                                  @foreach($subsidiaries as $subsidiary)
                                  <div class="col-special-ship-address">
                                    <div class="card">
                                      <div class="card-header">
                                        <h5 class="my-0">{{ $subsidiary->name }}</h5>
                                      </div>
                                      <div class="card-body">          
                                        <ul class="list-unstyled mb-4">
                                          <li>
                                            <span class="span-addresses-tr">Direccion</span>: 
                                            {{ $subsidiary->full_address }}
                                          </li>
                                          <li><span class="span-addresses-tr">Provincia</span>: {{ $subsidiary->city->state->name }}</li>
                                          <li><span class="span-addresses-tr">Codigo postal</span>: {{ $subsidiary->postal_code }}</li>
                                          <li><span class="span-addresses-tr">Pais</span>: Argentina</li>
                                        </ul>
                                        <button class="btn btn-block btn-outline-primary store-location"
                                        data-address="{{ $subsidiary->full_address . ', ' . $subsidiary->postal_code }}">
                                          Seleccionar
                                        </button>          
                                      </div>
                                    </div>
                                  </div>
                                  @endforeach
                                </div>
                              </div>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>
                <!-- END STEP CONTENT-->
              </fieldset>
            </div>
            <!-- END STEP 2-->
            <!-- BEGIN STEP 3-->
            <div class="tsf-step step-3 payment-tr">
              <fieldset>
                <legend>Como lo pagas?</legend>
                <!-- BEGIN STEP CONTENT-->
                <div class="tsf-step-content">
                  <ul class="nav nav-pills">
                    @foreach($payment_methods as $payment_method)
                    @if(config('config.SHOW_PRICING'))
                      <li class="nav-item pill-1">
                        <a data-type="finish" class="nav-link gateways" data-toggle="tab" href="#pay-{{ $payment_method->id }}" 
                          data-id="{{ $payment_method->id }}" data-url="{{ route('checkout.gatewayextrainfo', $payment_method->id) }}">
                          {{ $payment_method->name }}
                        </a>
                      </li>
                    @else
                      @if($payment_method->is_offline)
                        <li class="nav-item pill-1">
                          <a data-type="finish" class="nav-link gateways" data-toggle="tab" href="#pay-{{ $payment_method->id }}" 
                            data-id="{{ $payment_method->id }}" data-url="{{ route('checkout.gatewayextrainfo', $payment_method->id) }}">
                            {{ $payment_method->name }}
                          </a>
                        </li>
                      @endif
                    @endif
                    @endforeach
                  </ul>
                  
                  @include('checkout::partials.form-final')

                  @if(config('config.SHOW_PRICING'))
                    @include('checkout::partials.card-form')
                  @endif
                </div>
                <!-- END STEP CONTENT-->

              </fieldset>
            </div>
            <!-- END STEP 3-->
            <!--</form>-->
          </div>
          <!-- END CONTENT-->
          {{--<!-- BEGIN CONTROLS-->
          <div class="tsf-controls ">
            <!-- BEGIN PREV BUTTTON-->
            <button type="button" data-type="prev" class="btn btn-left ">
                            <i class="fa fa-chevron-left"></i> Anterior
                        </button>
            <!-- END PREV BUTTTON-->
            <!-- BEGIN NEXT BUTTTON-->
            <button type="button" data-type="next" class="btn btn-right ">
                            Siguiente <i class="fa fa-chevron-right"></i>
                        </button>
            <!-- END NEXT BUTTTON-->
            <!-- BEGIN FINISH BUTTTON-->
            <button type="submit" data-type="finish" class="btn btn-right">
                            Finalizar
                        </button>
            <!-- END FINISH BUTTTON-->
          </div>
          <!-- END CONTROLS-->--}}
        </div>
        <!-- END STEP CONTAINER -->


      </div>


@section('checkout-javascripts')
  <!--<script src="assets/vendor/jquery/dist/jquery.min.js"></script>-->
  <script src="{{asset('transversal/plugins/wizard/js/tsf-wizard.bundle.js')}}"></script>

  <script>
    var tsf1;
    $(function() {
      pageLoadScript();
      
      /*$('#checkout-form').submit(function(e) { 
        e.preventDefault(); // Cancel the submit
        return false;
      });*/

      $("#invoice-checkbox").change(function () {
          
            if ($(this).is(":checked")) {
                $("#invoice-data").hide();
                $("#invoice-data").show();
            } else {
              $("#invoice-data").show();
              $("#invoice-data").hide();                
            }
      });
    });

    

    function pageLoadScript() {
      tsf1 = $('.tsf-wizard-1').tsfWizard({
        stepEffect: 'slideLeftRight',
        stepStyle: 'style6',
        navPosition: 'top',
        validation: true,
        stepTransition: true,
        showButtons: true,
        showStepNum: true,
        manySteps: true,
        height: 'auto',
        disableSteps: 'none',
        prevBtn: '<i class="fa fa-chevron-left"></i> Anterior',
        nextBtn: 'Siguiente <i class="fa fa-chevron-right"></i>',
        finishBtn: 'FINALIZAR',
        onBeforeNextButtonClick: function(e, validation) {
          // habilita nuevamente gateways desabilitados anteriormente
          var gateways = $('a.gateways');
          for (var i = 0; i < gateways.length; i++) {
            $(gateways[i]).data('disabled', false);
            $(gateways[i]).removeClass('disabled');
          }

          //for return please write below code
          //  e.preventDefault();
        },
        onAfterNextButtonClick: function(e, from, to, validation) {
          /*console.log('onAfterNextButtonClick');
          console.log('validation ' + from + ' to ' + to);*/
        },
        onBeforePrevButtonClick: function(e, from, to) {
          /*console.log('onBeforePrevButtonClick');
          console.log('from ' + from + ' to ' + to);*/
          //  e.preventDefault();

        },
        onAfterPrevButtonClick: function(e, from, to) {
          /*console.log('onAfterPrevButtonClick');
          console.log('validation ' + from + ' to ' + to);*/
        },
        onBeforeFinishButtonClick: function(e, validation) {
          /*console.log('onBeforeFinishButtonClick');
          console.log('validation ' + validation);   */       
          //e.preventDefault();
        },
        onAfterFinishButtonClick: function(e, validation) {
          /*console.log('onAfterFinishButtonClick');
          console.log('validation ' + validation);*/
          //e.preventDefault();
          //$('.tsf-wizard').css('opacity', 0.4);
          //$('#end-checkout').removeClass('disabled');
          
        }
      });

    }
  </script>
  
@endsection