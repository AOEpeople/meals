Mealz.prototype.exportTransactions = function () {

    $(function () {
        $('.date-range-picker').daterangepicker({
            startDate: $('input[name="min-date"]').val(),
            endDate: $('input[name="max-date"]').val()
        }, function (start, end, label) {
            window.location.replace($('input[name="page-url"]').val() + '/' + start.format('YYYY-MM-DD') + '&' + end.format('YYYY-MM-DD'));
        });
    });

    $('.pdf-export').on('click', function (e) {
        var url = $('input[name="export-url"]').val() + '/' + $('input[name="min-date"]').val().replace(/\//g, '-') + '&' + $('input[name="max-date"]').val().replace(/\//g, '-');
        window.open(url);
    });
};
