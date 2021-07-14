const DataTable = require( 'datatables.net/js/jquery.dataTables' );
$.fn.dataTable = DataTable;

// Provide access to the host jQuery object (circular reference)
DataTable.$ = $;

// With a capital `D` we return a DataTables API instance rather than a
// jQuery object
$.fn.DataTable = function ( opts ) {
    return $(this).dataTable( opts ).api();
};

Mealz.prototype.enableSortableTables = function () {
    $('.table-sortable').DataTable({
        'aaSorting': [], // Disable initial sort
        paging: false,
        searching: false,
        info: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false
        }]
    });
};
