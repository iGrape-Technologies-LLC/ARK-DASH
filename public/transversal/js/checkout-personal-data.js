$(function () {                
    $('#ajax-profile-form').on('submit', function (e) {
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
                  //toastr.success(result.msg);                      
                  //$('#profile_message').fadeIn(1000);
                  tsf1.nextStep();
                  return true;
              } else {
                  //toastr.error(result.msg);                      
                  $('#profile_message_error').fadeIn(1000);
                  return true;                      
              }              
          }, error: function(e){            
              $('#profile_message_error').fadeIn(1000);
              return true;
          }, complete: function(){
              //$('#btn_submit').text("Enviar");                  
          }
        });
    });
});