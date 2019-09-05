Mealz.prototype.exportTransactions = function () {

    $(function () {
        $('#daterangepicker').daterangepicker({
            startDate: $('input[name="min-date"]').val(),
            endDate: $('input[name="max-date"]').val()
        }, function (start, end, label) {
            window.location.replace($('input[name="page-url"]').val() + '/' + start.format('YYYY-MM-DD') + '&' + end.format('YYYY-MM-DD'));
        });
    });
};
