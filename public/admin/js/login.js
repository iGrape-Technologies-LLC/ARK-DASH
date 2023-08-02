$(function() {
    

   	$('form#form').submit(function(e) {
      e.preventDefault();
       $.ajax({
        method: $(this).attr('method'),
        url: $(this).attr('action'),
        data: $(this).serialize(),
        complete: function(){            
              /*$(":submit").html(button_txt);
              $(":submit").removeAttr("disabled");*/
        },
        success: function(res) {
	          console.log(res)
	          if(res.success) {
	            //window.location = res.url;
	          } else {
	            //
	          }
        },
        error: function(err) {
          console.log(err);          
        }
      });
  	});

});
