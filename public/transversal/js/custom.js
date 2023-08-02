$(function() {		
    $.ajaxSetup({
            beforeSend: function(jqXHR, settings) {
                if (settings.url.indexOf('http') === -1) {
                    settings.url = base_path + settings.url;
                }
            },
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('a.add_favorite_item').click(function(e) {
        e.preventDefault();
        
        var article_id = $(this).data('value');
        var url = $(this).data('url');
        var button = $(this);
        // funcion definida en checkout.js
        toggleFavorite(url, article_id, function(res) {            
            if(button.hasClass('favorited')){
                button.removeClass('favorited');      
                toastr.success("Eliminado de favoritos");
            } else{
                button.addClass('favorited');      
                toastr.success("Agregado a favoritos");
            }                        
          //window.location.reload();
        }, function(err){
          toastr.error("No se puede agregar el producto a favoritos en estos momentos");
        });
    });

    /*$('a.remove_favorite_item').click(function(e) {
        e.preventDefault();

        var article_id = $(this).data('value');
        var url = $(this).data('url');
        var button = $(this);
        // funcion definida en checkout.js
        toggleFavorite(url, article_id, function() {
          button.removeClass('favorited');      
          toastr.success("Eliminado de favoritos");
          //window.location.reload();
        }, function(err){
          toastr.error("No se puede agregar el producto a favoritos en estos momentos");
        });
    });*/

    toastr.options.preventDuplicates = false;
    toastr.options.timeOut = 3000; 
    toastr.options.positionClass= 'toast-top-right';
    toastr.options.closeButton = true;
    toastr.options.progressBar = false;

    /*===================================================================================*/
    /*  LAZY LOAD IMAGES USING ECHO
    /*===================================================================================*/
    $(document).ready(function(){
        echo.init({
            offset: 100,
            throttle: 250,
            unload: false
        });
    });
});


function toggleFavorite(url, article_id, callback, callbackFail = null) {
  $.ajax({
    type: 'POST',
    url: url,
    data: {
      article_id: article_id
    },
    timeout: 20000,
    success: function(res) {
      console.log(res)
      if(res.success) {
        callback(res);
      }
    },
    error: function(err) {
      console.log(err);
      if(callbackFail != null) callbackFail(err);
    }
  });
}