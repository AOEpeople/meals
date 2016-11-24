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