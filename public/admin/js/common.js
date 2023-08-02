var button_txt;

$(function() {
	"use strict";
	//CustomJs();
    
    //No ponemos Loader
    /*setTimeout(function() {
        $('.page-loader-wrapper').fadeOut();
    }, 50);*/

    //PACE para los ajax
    $(document).ajaxStart(function() {
        Pace.restart();
    });	

    // shortcut modal quick form
    $(document).on('click', '.btn-modal', function(e) {
        e.preventDefault();
        var container = $(this).data('container');

        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
            },
        });
    });
    

});

var buttonTxt;

function buttonEffectCharging(element){
	buttonTxt = element.html();
    element.html('<i class="fa fa-spinner fa-spin"></i> <span>Procesando</span>');
    element.attr('disabled', 'disabled');
}

function buttonEffectNormal(element){
    element.html(buttonTxt);
    element.removeAttr("disabled");
}


// Sparkline
function initSparkline() {
	$(".sparkline").each(function() {
		var $this = $(this);
		$this.sparkline('html', $this.data());
    });
    
    // block-header bar chart js
    $('.bh_visitors').sparkline('html', {
        type: 'bar',
        height: '42px',
        barColor: '#a27ce6',
        barWidth: 5,
    });
    $('.bh_visits').sparkline('html', {
        type: 'bar',
        height: '42px',
        barColor: '#3eacff',
        barWidth: 5,
    });
    $('.bh_chats').sparkline('html', {
        type: 'bar',
        height: '42px',
        barColor: '#50d38a',
        barWidth: 5,
    });
}


// Costom js
function CustomJs() {

	// sidebar navigation
	$('.main-menu').metisMenu();

	  /*sidebar nav scrolling*/
	 /*$('#left-sidebar .sidebar-scroll').slimScroll({
	 	height: 'calc(100vh - 65px)',
	 	wheelStep: 10,
	 	touchScrollStep: 50,
	 	color: '#efefef',
	 	size: '2px',
	 	borderRadius: '3px',
	 	alwaysVisible: false,
	 	position: 'right',
	 });*/

	// cwidget scroll
	$('.cwidget-scroll').slimScroll({
		height: '263px',
		wheelStep: 10,
		touchScrollStep: 50,
		color: '#efefef',
		size: '2px',
		borderRadius: '3px',
		alwaysVisible: false,
		position: 'right',
	});

	// toggle fullwidth layout
	$('.btn-toggle-fullwidth').on('click', function() {
		if(!$('body').hasClass('layout-fullwidth')) {
			$('body').addClass('layout-fullwidth');
			$(this).find(".fa").toggleClass('fa-arrow-left fa-arrow-right');

		} else {
			$('body').removeClass('layout-fullwidth');
			$(this).find(".fa").toggleClass('fa-arrow-left fa-arrow-right');
		}
	});

	// off-canvas menu toggle
	$('.btn-toggle-offcanvas').on('click', function() {
		$('body').toggleClass('offcanvas-active');
	});

	$('#main-content').on('click', function() {
		$('body').removeClass('offcanvas-active');
	});

	// adding effect dropdown menu
	$('.dropdown').on('show.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).animate({
			top: '100%'
		}, 200);
	});

	$('.dropdown').on('hide.bs.dropdown', function() {
		$(this).find('.dropdown-menu').first().stop(true, true).animate({
			top: '80%'
		}, 200);
	});

	// navbar search form
	$('.navbar-form.search-form input[type="text"]')
	.on('focus', function() {
		$(this).animate({
			width: '+=50px'
		}, 300);
	})
	.on('focusout', function() {
		$(this).animate({
			width: '-=50px'
		}, 300);
	});

	// Bootstrap tooltip init
	if($('[data-toggle="tooltip"]').length > 0) {
		$('[data-toggle="tooltip"]').tooltip();
	}

	if($('[data-toggle="popover"]').length > 0) {
		$('[data-toggle="popover"]').popover();
	}

	$(window).on('load', function() {
		// for shorter main content
		if($('#main-content').height() < $('#left-sidebar').height()) {
			$('#main-content').css('min-height', $('#left-sidebar').innerHeight() - $('footer').innerHeight());
		}
	});
	$(window).on('load resize', function() {
		if($(window).innerWidth() < 420) {
			$('.navbar-brand logo.svg').attr('src', '../assets/images/logo-icon.svg');
		} else {
			$('.navbar-brand logo-icon.svg').attr('src', '../assets/images/logo.svg');
		}
    });
    
    // Select all checkbox
    $('.select-all').on('click',function(){
    
        if(this.checked){
            $(this).parents('table').find('.checkbox-tick').each(function(){
            this.checked = true;
            });
        }else{
            $(this).parents('table').find('.checkbox-tick').each(function(){
            this.checked = false;
            });
        }
    });

    $('.checkbox-tick').on('click',function(){   
        if($(this).parents('table').find('.checkbox-tick:checked').length == $(this).parents('table').find('.checkbox-tick').length){
            $(this).parents('table').find('.select-all').prop('checked',true);
        }else{
            $(this).parents('table').find('.select-all').prop('checked',false);
        }
    });

}
// toggle function
$.fn.clickToggle = function( f1, f2 ) {
	return this.each( function() {
		var clicked = false;
		$(this).bind('click', function() {
			if(clicked) {
				clicked = false;
				return f2.apply(this, arguments);
			}

			clicked = true;
			return f1.apply(this, arguments);
		});
	});
};

var rangesPast = {};
rangesPast[LANG.today] = [moment(), moment()];
rangesPast[LANG.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
rangesPast[LANG.last_7_days] = [moment().subtract(6, 'days'), moment()];
rangesPast[LANG.last_30_days] = [moment().subtract(29, 'days'), moment()];
rangesPast[LANG.this_month] = [moment().startOf('month'), moment().endOf('month')];
rangesPast[LANG.last_month] = [
    moment()
        .subtract(1, 'month')
        .startOf('month'),
    moment()
        .subtract(1, 'month')
        .endOf('month'),
];
rangesPast[LANG.this_month_last_year] = [
    moment()
        .subtract(1, 'year')
        .startOf('month'),
    moment()
        .subtract(1, 'year')
        .endOf('month'),
];
rangesPast[LANG.this_year] = [moment().startOf('year'), moment().endOf('year')];
rangesPast[LANG.last_year] = [
    moment().startOf('year').subtract(1, 'year'), 
    moment().endOf('year').subtract(1, 'year') 
];

var dateRangeSettings = {
    ranges: rangesPast,
    startDate: moment().startOf('month'),
    endDate: moment().endOf('month'),
    locale: {        
        cancelLabel: LANG.clear,
        applyLabel: LANG.apply,
        customRangeLabel: LANG.custom_range,
        format: moment_date_format,
        toLabel: '~',
    },
};
var rangesFuture = {};
rangesFuture[LANG.this_month] = [moment().startOf('month'), moment().endOf('month')];
rangesFuture[LANG.next_month] = [
    moment()
        .add(1, 'month')
        .startOf('month'),
    moment()
        .add(1, 'month')
        .endOf('month'),
];
rangesFuture[LANG.next_7_days] = [moment(), moment().add(6, 'days'), moment()];
rangesFuture[LANG.this_year] = [moment().startOf('year'), moment().endOf('year')];
var dateRangeSettingsFuture = {
    ranges: rangesFuture,
    startDate: moment().startOf('month'),
    endDate: moment().endOf('month'),
    locale: {        
        cancelLabel: LANG.clear,
        applyLabel: LANG.apply,
        customRangeLabel: LANG.custom_range,
        format: moment_date_format,
        toLabel: '~',
    },
};

