var transaction = {
  shipping_address_id: null,
}

$(function() {
  actualizarCarts();

  $('div#card-form').hide();

  $('.credit-card').inputmask('9999 9999 9999 9999', { placeholder: '____ ____ ____ ____' });

  $(".gateways").click(function() {
    var gateway_id = $(this).data('id');
    var url = $(this).data('url');
    var disabled = $(this).data('disabled');

    if(!disabled) {
      $("#gateway_id").val(gateway_id);
      $('#end-checkout').removeClass('disabled');

      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'html',
        timeout: 20000,
        success: function(extra_info) {
          var first_char = extra_info.toString().split('')[0];
          if(first_char == '#') {
            var extra_info_form = $('div' + extra_info).html();
            $('div.payment-extra-info').html(extra_info_form);
          } else {
            $('div.payment-extra-info').html(extra_info);
          }
        },
        error: function(err) {
          console.log(err);
          toastr.error("Ha ocurrido un error al recuperar los métodos de pago");
        }
      });
    }
  });

  $(".addresses-type").click(function(){
    $("#shipping_address_type").val($(this).data('id'));
  });

  $('a#end-checkout').click(function(e) {
      e.preventDefault();

      //Acadeberiamos hacer cosas

      $("#ajax-final-form").submit();
  });

  $('#ajax-final-form').on('submit', function (e) {
      e.preventDefault();

      var href = $(this).data('href');
      var data = $(this).serialize();

      //Chequeo que si es tarjeta con Prisma, valido los campos
      if( $('input[name="gateway_id"]').val() == 3){
        $("#card_number").parsley().validate();
        $("#card_expiration_month").parsley().validate();
        $("#card_expiration_year").parsley().validate();
        $("#security_code").parsley().validate();

          if(
                !$("#card_number").parsley().isValid() ||
                !$("#card_expiration_month").parsley().isValid() ||
                !$("#card_expiration_year").parsley().isValid() ||
                !$("#security_code").parsley().isValid() )
            {
              return false;
            }
      }

      var currentButtonText = $('a#end-checkout').text();
      $('a#end-checkout').html('<i class="fa fa-spinner fa-spin"></i> <span></span>');
      $('a#end-checkout').addClass('disabled');

      $.ajax({
        type: 'POST',
        url: href,
        data: data,
        dataType: 'json',
        timeout: 40000,
        beforeSend: function(){
          $('#form_message').fadeOut();
          $('#form_message_error').fadeOut();

          $("#checkout-tr").addClass("disabledContent");
        },
        complete: function() {
          $('a#end-checkout').text(currentButtonText);
          $('a#end-checkout').removeClass('disabled');
        },
        success: function(res) {
          if (res.success == true) {
            $('#form_message').fadeIn(1000);
            $("#end-checkout").remove();
            window.location = res.url;
          } else{
            if(res.msg) {
              toastr.error(res.msg);
            }
            $('#form_message_error').fadeIn(1000);
            $("#checkout-tr").removeClass("disabledContent");
            return true;
          }
        },
        error: function(err) {
          console.log(err);
          $('#form_message_error').fadeIn(1000);
          $("#checkout-tr").removeClass("disabledContent");
        }

      });
  });


  $('a.add_cart_item').click(function(e) {
    e.preventDefault();

    var article_property_id = $(this).data('value');
    var url = $(this).data('url');
    var qty = $(this).data('qty');

    addItemToCart(url, article_property_id, qty, function() {
      actualizarCarts();
      toastr.success("Producto agregado al carrito");
    }, function(err){
      console.log(err)
      toastr.error("No se puede agregar el producto al carrito en estos momentos");
    });
  });

  //Funcion que se ejecuta en Page Producto
  $('a#add_cart_one_page').click(function(e) {
    e.preventDefault();

    $("#error_page_product").hide();

    var article_property_id = $(this).data('value');
    var url = $(this).data('url');
    var redirect_uri = $(this).data('redirect_uri');

    var qty = $('#qty').val();
    if(typeof qty == 'undefined') qty = 1;


    addItemToCart(url, article_property_id, qty, function() {
      if(redirect_uri != "") window.location = redirect_uri;
      actualizarCarts();
      toastr.success("Producto agregado al carrito");
    }, function(err){
      console.log(err)
      $("#error_page_product").html(err);
      $("#error_page_product").show();
    });
  });

  // agregar a favoritos
  $('span.cart-slide').on('click', 'a.add_favorite_item', function(e) {
    e.preventDefault();

    var article_id = $(this).data('value');
    var url = $(this).data('url');
    var button = $(this);
    toggleFavorite(url, article_id, function() {
      if(button.hasClass('favorited')){
          button.removeClass('favorited');
          toastr.success("Eliminado de favoritos");
      } else{
          button.addClass('favorited');
          toastr.success("Agregado a favoritos");
      }
    }, function(err){
      toastr.error("No se puede agregar el producto a favoritos en estos momentos");
    });
  });

  // eliminar de favoritos
  /*$('span.cart-slide').on('click', 'a.remove_favorite_item', function(e) {
    e.preventDefault();

    var article_id = $(this).data('value');
    var url = $(this).data('url');
    var button = $(this);
    toggleFavorite(url, article_id, function() {
      button.addClass('favorited');
      toastr.success("Eliminado de favoritos");
    }, function(err){
      toastr.error("No se puede agregar el producto a favoritos en estos momentos");
    });
  });*/

  $('span.cart-slide').on('click', 'span.delete-btn', function(e) {
    var url = $(this).data('url');

    removeCartItem(url, function() {
      $('div.modal#cart-slide-modal').modal('hide');
      actualizarCarts();
      //toastr.success("Producto eliminado del carrito");
    }, function(err){
      toastr.error("No se pudo eliminar el producto del carrito");
    });
  });

  $('span.cart-slide').on('click', 'button#increase-quantity', function() {
    var url = $(this).data('url');
    var article_property_id = $(this).data('id');
    var article_price = $(this).data('price');

    var currentQuantity = 0;
    try {
      currentQuantity = parseInt($('input#current-quantity-' + article_property_id).val());
    } catch(e) {
      console.log(e);
    }
    var newQuantity = currentQuantity + 1;
    var article_total = newQuantity * article_price;
    $('input#current-quantity-' + article_property_id).val(newQuantity);
    $('div.total-price#total-' + article_property_id).html(formatMoney(article_total));

    changeItemQuantity(url, function() {
      if(newQuantity == 0) {
        $('#cart-slide-modal').modal('hide');
        updateShoppingCartSlide();
      }
      updateShoppingCartLink();
      updateQuantityBadge();
    }, function(){
       var article_total = currentQuantity * article_price;
      $('input#current-quantity-' + article_property_id).val(currentQuantity);
      $('div.total-price#total-' + article_property_id).html(formatMoney(article_total));
    });
  });

  $('.quantity-article').on('focusin', function(){
      $(this).data('val', $(this).val());
  });

  $('span.cart-slide').on('change', ".quantity-article", function(e){
      var prevQuantity = $(this).data('val');
      var quantity = $(this).val();
      var quantity_data_url = $(this).data('url');
      var article_price = $(this).data('price');
      var article_property_id = $(this).data('id');

      var article_total = quantity * article_price;
      $('div.total-price#total-' + article_property_id).html(formatMoney(article_total));

      var input = $(this);

      setItemQuantity(quantity_data_url, quantity, function(){
        if(quantity == 0) {
          $('#cart-slide-modal').modal('hide');
          updateShoppingCartSlide();
        }
        updateShoppingCartLink();
        updateQuantityBadge();
      }, function(){
        toastr.error("No se puede agregar esa cantidad");
        var article_total = prevQuantity * article_price;
        $('div.total-price#total-' + article_property_id).html(formatMoney(article_total));
        input.val(prevQuantity);
      });
  });

  $('span.cart-slide').on('click', 'button#decrease-quantity', function() {
    var url = $(this).data('url');
    var article_property_id = $(this).data('id');
    var article_price = $(this).data('price');

    var currentQuantity = 0;
    try {
      currentQuantity = parseInt($('input#current-quantity-' + article_property_id).val());
    } catch(e) {
      console.log(e);
    }
    var newQuantity = currentQuantity - 1;
    var article_total = newQuantity * article_price;
    $('input#current-quantity-' + article_property_id).val(newQuantity);
    $('div.total-price#total-' + article_property_id).html(formatMoney(article_total));

    changeItemQuantity(url, function() {
      if(newQuantity == 0) {
        $('#cart-slide-modal').modal('hide');
        updateShoppingCartSlide();
      }
      updateShoppingCartLink();
      updateQuantityBadge();
    }, function(){
      //console.log('Fail')
    });
  });
});

function shippingTypeSelected(type) {
  $('input#shipping_type').val(type);
}

function actualizarCarts(){
  updateShoppingCartLink();
  updateShoppingCartSlide();
  updateQuantityBadge();
}

function changeItemQuantity(url, callback, callbackFail) {
  $.ajax({
    url: url,
    type: 'GET',
    timeout: 20000,
    dataType: 'json',
    beforeSend: function(){
      $("button#increase-quantity").attr("disabled", 'disabled');
      $("button#decrease-quantity").attr("disabled", 'disabled');
      $(".quantity-article").attr("disabled", 'disabled');

    },
    complete: function(){
      $("button#increase-quantity").removeAttr("disabled");
      $("button#decrease-quantity").removeAttr("disabled");
      $(".quantity-article").removeAttr("disabled");
    },
    success: function(res) {
      if(res.success) {
        callback();
      } else{
        callbackFail();
        toastr.error("No se puede agregar esa cantidad de productos");
        //console.log(res.msg)
      }
    },
    error: function(err) {
      console.log(err);
      toastr.error("No se puede agregar esa cantidad de productos al carrito");
    }
  });
}

function setItemQuantity(url, quantity, callback, callbackFail) {
  var data = {
    quantity : quantity
  }

  $.ajax({
    url: url,
    data: data,
    type: 'POST',
    timeout: 20000,
    dataType: 'json',
    beforeSend: function(){
      $("button#increase-quantity").attr("disabled", 'disabled');
      $("button#decrease-quantity").attr("disabled", 'disabled');
      $(".quantity-article").attr("disabled", 'disabled');
    },
    complete: function(){
      $("button#increase-quantity").removeAttr("disabled");
      $("button#decrease-quantity").removeAttr("disabled");
      $(".quantity-article").removeAttr("disabled");
    },
    success: function(res) {
      if(res.success) {
        callback();
      } else{
        callbackFail();
        toastr.error("No se puede agregar esa cantidad de productos");
        //console.log(res.msg)
      }
    },
    error: function(err) {
      console.log(err);
    }
  });
}

function updateQuantityBadge() {
  getCartData(function(err, cart) {
    if(!err) {
      var quantity_total = 0;
      if(cart){
        for (var i = 0; i < cart.article_properties.length; i++) {
          quantity_total += cart.article_properties[i].pivot.quantity;
        }

        $('span.cart-quantity').html(quantity_total);
      } else{
        $('span.cart-quantity').html('0');
      }
    }
  });
}

function addItemToCart(url, article_property_id,  qty, callback, callbackFail = null) {
  var currentButtonText = $('#add_cart_one_page').text();
  $('#add_cart_one_page').html('<i class="fa fa-spinner fa-spin"></i> <span></span>');
  $('#add_cart_one_page').addClass('disabled');
  if(qty == null) qty = 1;
  $.ajax({
    type: 'POST',
    url: url,
    data: {
      article_property_id: article_property_id,
      quantity: qty
    },
    timeout: 20000,
    complete: function(){
        $('#add_cart_one_page').text(currentButtonText);
        $('#add_cart_one_page').removeClass('disabled');
    },
    success: function(res) {
      if(res.success) {
        callback();
      } else{
        if(callbackFail != null) callbackFail(res.msg);
      }
    },
    error: function(err) {
      if(callbackFail != null) callbackFail(err);
    }
  });
}



function removeCartItem(url, callback, callbackFail = null) {
  $.ajax({
    type: 'GET',
    url: url,
    timeout: 20000,
    success: function(res) {
      if(res.success) {
        callback();
      }
    },
    error: function(err) {
      console.log(err);
      if(callbackFail != null) callbackFail(err);
    }
  });
}

function updateShoppingCartLink() {
  var update_url = $('div.shopping-cart-link').data('url');

  $.ajax({
    url: update_url,
    type: 'GET',
    dataType: 'html',
    timeout: 20000,
    success: function(res) {
      $('span.cart-link').html(res);
    },
    error: function(err) {
      console.log(err);
      toastr.error("Error al actualizar el carrito");
    }
  });
}

function updateShoppingCartSlide() {
  var update_url = $('div.modal#cart-slide-modal').data('url');

  $.ajax({
    url: update_url,
    type: 'GET',
    dataType: 'html',
    success: function(res) {
      $('span.cart-slide').html(res);
    },
    error: function(err) {
      console.log(err);
      toastr.error("Error al actualizar el carrito");
    }
  });
}

// permite obtener los datos del carrito desde cualquier parte del sistema
function getCartData(callback) {
  // span definido en front.layouts.partials.javascripts
  var cart_data_url = $('span.url-parameter#get-cart-data').data('url');

  $.ajax({
    url: cart_data_url,
    type: 'GET',
    timeout: 20000,
    dataType: 'json',
    success: function(cart) {
      callback(null, cart);
    },
    error: function(err) {
      console.log(err);
      toastr.error("Error al recuperar datos del carrito");
      callback(err);
    }
  });
}

// calcula los totales del carrito
function getCartTotals(callback) {
  // span definido en front.layouts.partials.javascripts
  var cart_totals_url = $('span.url-parameter#cart-totals').data('url');

  $.ajax({
    url: cart_totals_url,
    type: 'GET',
    timeout: 20000,
    dataType: 'json',
    success: function(totals) {
      callback(null, totals);
    },
    error: function(err) {
      callback(err);
    }
  });
}
