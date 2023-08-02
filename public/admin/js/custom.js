$(function() {
	"use strict";

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

    // validation needs name of the element
    $('.multiselect').multiselect(
        { 
            nonSelectedText:'Ninguno',
            allSelectedText: 'Todos'
        }
    );

    // ______________Full screen
    $("#fullscreen-button").on("click", function toggleFullScreen() {
        if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
          if (document.documentElement.requestFullScreen) {
            document.documentElement.requestFullScreen();
          } else if (document.documentElement.mozRequestFullScreen) {
            document.documentElement.mozRequestFullScreen();
          } else if (document.documentElement.webkitRequestFullScreen) {
            document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
          } else if (document.documentElement.msRequestFullscreen) {
            document.documentElement.msRequestFullscreen();
          }
        } else {
          if (document.cancelFullScreen) {
            document.cancelFullScreen();
          } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
          } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
          }
        }
      })

    $('.selectize').selectize();

    // initialize after multiselect
    $('.validate').parsley();      

    $.extend($.fn.fileinput.defaults, {
        language: 'es',
        theme: "fa",
        showRemove: false,
        showUpload: false,
        showClose: false,
        browseOnZoneClick: true,
        browseClass: 'btn btn-common',
        autoOrientImage: false,                
        //required: true,
        //msgFileRequired: 'Por favor, seleccione un archivo',        
        maxFileSize: 25600,
        msgSizeTooLarge: 'Disculpe, las imagenes no pueden pesar mas de 25MB',
        msgUploadError: 'Disculpe, ocurrió un error subiendo el archivo',
        previewZoomButtonIcons: {
            toggleheader: '<i class="fa fa-arrows-alt-v"></i>',
            borderless: '<i class="fa fa-external-link-alt"></i>',
            close: '<i class="fa fa-times"></i>'
        },  
    }); 
	
	$.extend($.fn.dataTable.defaults, {
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        iDisplayLength: 25,        
        language: {
            search: '',
            searchPlaceholder: LANG.search+'...',
            lengthMenu: LANG.show + ' _MENU_ ' + LANG.entries,
            emptyTable: LANG.table_emptyTable,
            info: LANG.table_info,
            infoEmpty: LANG.table_infoEmpty,
            loadingRecords: LANG.table_loadingRecords,
            processing: LANG.table_processing,
            zeroRecords: LANG.table_zeroRecords,
            paginate: {
                first: LANG.first,
                last: LANG.last,
                next: LANG.next,
                previous: LANG.previous,
            },
        },
        dom: '<"row margin-bottom-20 text-center"<"col-sm-2"l><"col-sm-7 pt3"B><"col-sm-3 text-center"f> r>tip',
        buttons: {
            buttons: [
                { extend: 'copy', className: 'btn btn-outline-secondary btn-sm', text: LANG.copy, exportOptions: {columns: ':not(.no-print)'} },
                { extend: 'csv', className: 'btn btn-outline-secondary btn-sm', text: LANG.export_to_csv, exportOptions: {columns: ':not(.no-print)'} },
                { extend: 'excel', className: 'btn btn-outline-secondary btn-sm', text: LANG.export_to_excel, exportOptions: {columns: ':not(.no-print)'} },
                { extend: 'pdf', className: 'btn btn-outline-secondary btn-sm', text: LANG.export_to_pdf, exportOptions: {columns: ':not(.no-print)'} },
                { extend: 'print', className: 'btn btn-outline-secondary btn-sm', text: LANG.print, exportOptions: {columns: ':not(.no-print)'} }
            ]
        }
     
    });
});

