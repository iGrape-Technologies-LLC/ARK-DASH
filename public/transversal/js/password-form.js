$(function () {                       
    $('#ajax-password-form').on('submit', function (e) {
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
                  $('#password_message').fadeIn(1000);
                  return true;
              } else {
                  //toastr.error(result.msg);                      
                  $('#password_message_error').fadeIn(1000);
                  return true;                      
              }              
          }, error: function(e){        
              $('#password_message_error').fadeIn(1000);
              return true;
          }, complete: function(){
              //$('#btn_submit').text("Enviar");                  
          }
        });
    });
  });