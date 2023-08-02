$(function () {   
    reload_addresses();                    
    $('#ajax-address-form').on('submit', function (e) {
        e.preventDefault();            
        var href = $(this).data('href');
        var data = $(this).serialize();        
        console.log(data)
        $.ajax({
          type: 'POST',
          url: href,
          data: data,
          dataType: 'json',
          success: function (result) {        
                  
              if (result.success == true) {
                  //toastr.success(result.msg);        
                  reload_addresses();  
                  $('#ajax-address-form')[0].reset();
                  $('#ajax-address-form').parsley().reset();          
                  $('#address_message').fadeIn(1000);
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

function reload_addresses(){
  $.ajax({
    url: addressesUrl,
    type: 'GET',
    success: function(view) {
        $("#addresses_list").html(view);
    }
  });
  
}