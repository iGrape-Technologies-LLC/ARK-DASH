var table;
$(function() {      
    table = $('#table').DataTable({
        ajax: {
          url: route_table
        },
        processing: true,
        serverSide: true,
        orderable: false,
        columns: [
            /*{ data: 'id', name: 'id' },*/
            { data: 'causer', name: 'causer', orderable: 'false' },
            { data: 'description', name: 'description', orderable: 'false' },
            { data: 'subject_type', name: 'subject_type', orderable: 'false' },            
            { data: 'created_at', name: 'created_at', orderable: 'false' },
        ],
    });    
});