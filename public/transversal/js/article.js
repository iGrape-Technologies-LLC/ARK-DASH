$(function() {
    $('select#optionSelector').change(function() {
      var price = $('select#optionSelector option:selected').data('price');
      var stock = $('select#optionSelector option:selected').data('stock');

      if(price) {
        $('div.item span.price').text(price);
      }

      if(stock) {
        $('span#available_stock').text(stock);
      }
    });

    $('.product-atribute').click(function(){
    	thisRadio = $(this);
	    if (thisRadio.hasClass("checked")) {
	        thisRadio.removeClass("checked");
	        thisRadio.prop('checked', false);
	        $( this ).trigger( "change" );
	    } else { 
	        thisRadio.prop('checked', true);
	        thisRadio.addClass("checked");
	    };
    });

   	

    $('.product-atribute').change(function(){    	
    	var data = {
	    	article: $(this).data('article'),
	    	//property : $(this).data('property'),	    	
	    	//value: $(this).val(),
	    }
	    askForAtributes(data);
	 });

    var data = {
    	article: $('#articlesProperties').data('article'),   	
    	//value: $(this).val(),
    }
    askForAtributes(data);

    
  });

function askForAtributes(data){
		var properties = [];
    	$('.product-atribute').each(function () {
	        if ($(this).hasClass('checked')) {
	            properties.push($(this).val());
	        }
	    });

	    data.properties = properties;

	    var href = $('#articlesProperties').data('href');	    
	    
	    $("#articlesProperties").addClass("disabledContent");
	    $("#add_cart_one_page").addClass("disabled");

	    $.ajax({
	      	type: 'POST',
        	url: href,
        	data: data,
        	dataType: 'json',
        	timeout: 20000,
	      	complete: function(){
	      		$("#articlesProperties").removeClass("disabledContent");

	      	},
	      	success: function(res) {
	      		$("#add_cart_one_page").data('value', '');	      		
	        	if(res.success){
	        		$( ".product-atribute" ).each(function( index ) {
	        			$(this).prop('disabled', true);					  	
					});
					for(var j=0; j<res.data.length;j++){
						$('.product-atribute[data-property='+res.data[j]['property_id']+'][value=' +res.data[j]['id']+ ']').prop('disabled', false);
					}					
					if(res.article_property != null){
						$("#add_cart_one_page").removeClass("disabled");
						$("#add_cart_one_page").data('value', res.article_property.id);
						$(".price-article-tr").html(res.article_property.price_formatted);						
					} else{
						$('.price-article-tr').each(function () {							
					        $(".price-article-tr").html($(this).data('original-price'));
					    });
							
					}
	        	} else{

	        	}
	      	},
	      	error: function(err) {
	      		$("#add_cart_one_page").data('value', '');
	      		toastr.error("Ha ocurrido un error al buscar el stock del producto");	        	
	      	}
	    });
}