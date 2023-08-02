$(function () {       
    $('#form-newsletter').on('submit', function (e) {
        e.preventDefault();
        $('#btn-newsletter').text("Suscribiendose...");
        var href = $(this).data('href');
        var data = $(this).serialize();        
        $.ajax({
          type: 'POST',
          url: href,
          data: data,
          dataType: 'json',
          success: function (result) {                  
              if (result.success == true) {               
                  $('#newsletter-message').fadeIn(1000);
                  return true;
              } else {                      
                  $('#newsletter-message-error').fadeIn(1000);
                  return true;                      
              }              
          }, error: function(e){                       
              $('#newsletter-message-error').fadeIn(1000);
              return true;
          }, complete: function(){
              $('#btn-newsletter').text("Suscribirse");              
          }
        });
    });
  });

