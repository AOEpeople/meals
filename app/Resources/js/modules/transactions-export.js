Mealz.prototype.exportTransactions = function () {

    $(function () {
        $('.date-range-picker').daterangepicker({
            startDate: $('input[name="min-date"]').val(),
            endDate: $('input[name="max-date"]').val()
        }, function (start, end, label) {
            window.location.replace($('input[name="page-url"]').val() + '/' + start.format('YYYY-MM-DD') + '&' + end.format('YYYY-MM-DD'));
        });
    });

    $('.pdf-export').click(function (e) {
        $.ajax({
            method: 'GET',
            url: $('input[name="export-url"]').val(),
            dataType: 'json',
            success: function (data) {
            },
            error: function (xhr) {
                console.log(xhr.status + ': ' + xhr.statusText);
            }
        });
    });
};
