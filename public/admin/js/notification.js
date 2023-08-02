var table;
$(function() {      
    
    table = $('#table').DataTable({
        ajax: {
          url: route_table
        },
        processing: true,
        serverSide: true,
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'subject', name: 'subject' },            
            { data: 'message', name: 'message' }
        ],
    });    
});