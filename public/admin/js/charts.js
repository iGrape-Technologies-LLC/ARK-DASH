$(function() {
    "use strict";

    $(".chart-box").each(function() {
	    $.ajax({
	        method: 'GET',
        	url: $(this).attr('action'),
	        complete: function(){            
	              //
	        },
	        success: function(res) {
	          if(res.success) {
	          	if(res.data.length>0){
	          		var data = new Array();
		          	for(var j=0; j<res.data.length; j++){
			          	var dia = {
				          "dia": res.data[j]['date'],
				          "total": res.data[j]['total']
				        }
				        data.push(dia);
			        }		        

		            lineChart('graphSellsLastMonth', data, 'dia', 'total', 'Total');
	          	} else{
	          		var message = $("#graphSellsLastMonth").data('empty-message');
	          		console.log(message)
	          		$("#graphSellsLastMonth").html('<p class="text-center pt-50">'+message+'</p>')
	          	}
	          	
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

function lineChart(element, data, xkey, ykeys, labels){
    $('#'+element).empty();

    new Morris.Line({
      element: element,
      parseTime: false,
      data: data,
      xkey: xkey,
      stacked: true,
      hoverCallback: function(index, options, content, row) {	         
	       var hover = "<div class='morris-hover-row-label'>"+row.dia+"</div><div class='morris-hover-point' style='color: #A4ADD3'><p color:black>Total: $ "+row.total+"</p></div>";
	       return hover;
	  },
      ykeys: [ykeys],
      labels: [labels]
    });
}