$(function () {   
    reload_addresses();                    
    $('#ajax-address-form').on('submit', function (e) {
        e.preventDefault();            
        var href = $(this).data('href');
        var data = $(this).serialize();        
        
        $.ajax({
          type: 'POST',
          url: href,
          data: data,
          dataType: 'json',
          success: function (result) {        
                  
              if (result.success == true) {  
                  $('#newAddressModal').modal('hide');
                  reload_addresses();                    
                  $('#ajax-address-form')[0].reset();
                  $('#ajax-address-form').parsley().reset();       
                  $("#shipping_address_id").val(result.id);                                     
                  //tsf1.nextStep();
                  return true;
              } else {
                  //toastr.error(result.msg);                      
                  $('#address_message_error').fadeIn(1000);
                  return true;                      
              }              
          }, error: function(e){   
            console.log(e)             
              $('#address_message_error').fadeIn(1000);
              return true;
          }, complete: function(){
              //$('#btn_submit').text("Enviar");                  
          }
        });
    });
  });
/*function listenerAdressForm(){
  $(".btn-next").click(function(){
    tsf1.nextStep();
  });
}*/

function reload_addresses(){
  $.ajax({
    url: addressesUrl,
    type: 'GET',
    success: function(view) {
        $("#addresses_list").html(view);
        //listenerAdressForm();
    }, 
    error: function(e){
      toastr.error("Ha ocurrido un error al cargar las direcciones");
    }
  });  
}