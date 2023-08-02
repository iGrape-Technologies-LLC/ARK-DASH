$(function() {
  // definido en checkout::partials.form-checkout.blade.php
  var shipping_methods = $('span#shipping-methods-data').data('json');

  $('div#addresses_list').on('click', "button.addresses", function(e) {
    var address_id = $(this).data('id');
    var url = $(this).data('url');

    $("#shipping_address_id").val(address_id);

    $(this).removeClass('btn-outline-primary');
    $(this).addClass('btn-primary');

    $('div.shipping-costs').html('Calculando envio...');
    for (var i = 0; i < shipping_methods.length; i++) {
      var shipping_method = shipping_methods[i];

      $('button.shipping-methods-selector[data-id=' + shipping_method.id+ ']').addClass('disabled');
      $('button.shipping-methods-selector[data-id=' + shipping_method.id+ ']').attr('disabled', 'true');

      $.ajax({
        url: url,
        type: 'POST',
        data: {
          cart_id: $('input#cart_id').val(),
          carrier_id: shipping_method.id,
          address_id: address_id
        },
        dataType: 'json',
        timeout: 20000,
        success: function(shipping_data) {
          $('button.shipping-methods-selector[data-id=' + shipping_data.carrier_id+ ']').removeClass('disabled');
          $('button.shipping-methods-selector[data-id=' + shipping_data.carrier_id+ ']').removeAttr('disabled');
          $('div.shipping-costs#shipping-cost-' + shipping_data.carrier_id).html(formatMoney(shipping_data.cost));
          $('div.shipping-costs#shipping-cost-' + shipping_data.carrier_id).data('amount', shipping_data.cost);
        },
        error: function(err) {
          console.log(err);
          if(err.responseJSON && err.responseJSON.message) {
            toastr.clear();
            toastr.error(err.responseJSON.message);
          }
          $('div.shipping-costs').html('');
        }
      });
    }
  });

  $('button.shipping-methods-selector').click(function() {
    var carrier_payment_methods_url = $(this).data('shipping-payment-url');
    
    var shipping_method_id = $(this).data('id');
    $('input#shipping_method_id').val(shipping_method_id);
    $('input#shipping_type').val('address');
    var shipping_cost = $('div.shipping-costs#shipping-cost-' + shipping_method_id).data('amount');
    addShippingCost(shipping_cost);

    cleanAvailablePaymentMethods();
  
    $.ajax({
      type: 'POST',
      url: carrier_payment_methods_url,
      data: {
        carrier_id: shipping_method_id
      },
      dataType: 'json',
      timeout: 20000,
      success: function(payment_methods) {
        var gateways = $('a.gateways');
        for (var i = 0; i < gateways.length; i++) {
          var payment_method_id = $(gateways[i]).data('id');

          if(!payment_methods.includes(payment_method_id)) {
            $(gateways[i]).data('disabled', true);
            $(gateways[i]).addClass('disabled');
          }
        }
      },
      error: function(err) {
        console.log(err);
      }
    });

    tsf1.nextStep();
  });

  $('a.addresses-type').click(function() {
    var articles_total = $('dd.cart-articles-total').data('amount');
    var discount = $('dd.cart-discount').data('amount');
    var cart_total = articles_total - discount;
    $('input#shipping_address_id').val('');
    $('dd.cart-shipping-total').html('+ ' + formatMoney(0));
    $('strong.cart-total').data('amount', cart_total);
    $('strong.cart-total').html(formatMoney(cart_total));
    $('a#end-checkout').addClass('disabled');
  });

  $('button#search-locations').click(function(e) {
    e.preventDefault();

    var zip_code = $('input#locations-search-zipcode').val();

    if(zip_code.trim() == '') {
      toastr.error($(this).data('error'));
      return;
    }

    var previous_text = $(this).text();
    $(this).text('Buscando...');
    $(this).addClass('disabled');

    $.ajax({
      type: 'GET',
      url: $(this).data('url') + '?zip_code=' + zip_code + '&carrier_id=' + $(this).data('id') + '&cart_total=' + $(this).data('amount'),
      timeout: 20000,
      dataType: 'html',
      success: function(sucursales) {
        addShippingCost(0);
        $('input#shipping_address_extra').val('');
        $('input#shipping_method_id').val('');
        $('input#shipping_extra_zip').val('');
        $('div.carrier-locations').html(sucursales);
      },
      error: function(err) {
        console.log(err);
      },
      complete: function() {
        $('button#search-locations').text(previous_text);
        $('button#search-locations').removeClass('disabled');
      }
    });
  });

  $('div.carrier-locations').on('click', 'button.carrier-location', function(e) {
    e.preventDefault();

    var shipping_address_extra = $(this).data('address');
    $('input#shipping_address_extra').val(shipping_address_extra);
    var carrier_id = $(this).data('carrier');
    $('input#shipping_method_id').val(carrier_id);
    var zip_code = $(this).data('zipcode');
    $('input#shipping_extra_zip').val(zip_code);
    var shipping_cost = $(this).data('cost');
    $('input#shipping_type').val('pick_up_carrier');
    // function definida en shipping.js
    addShippingCost(shipping_cost);

    cleanAvailablePaymentMethods();

    tsf1.nextStep();
  });

  $('div.store-locations').on('click', 'button.store-location', function(e) {
    e.preventDefault();

    var shipping_address_extra = $(this).data('address');
    $('input#shipping_address_extra').val(shipping_address_extra);
    $('input#shipping_type').val('pick_up_store');
    $('input#shipping_extra_zip').val('');
    $('input#shipping_method_id').val('');
    addShippingCost(0);

    cleanAvailablePaymentMethods();

    tsf1.nextStep();
  });
});

function addShippingCost(shipping_cost) {
  var cart_total = $('.cart-articles-total').data('amount');
  cart_total += shipping_cost;

  $('.cart-shipping-total').html('+ ' + formatMoney(shipping_cost));
  $('.cart-total').data('amount', cart_total);
  $('.cart-total').html(formatMoney(cart_total));
  $('a#end-checkout').addClass('disabled');
}

function cleanAvailablePaymentMethods() {
  var gateways = $('a.gateways');
  for (var i = 0; i < gateways.length; i++) {
    $(gateways[i]).data('disabled', false);
    $(gateways[i]).removeClass('disabled');
    $(gateways[i]).removeClass('active');
    $(gateways[i]).removeClass('show');
  }
  $('a#end-checkout').addClass('disabled');
}
