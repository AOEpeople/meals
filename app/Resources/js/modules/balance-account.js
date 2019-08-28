function balanceAccount() {
    $('.button.balance-account').click(function () {
        var $container = $('[data-balance-account]');

        //$('#swapLink').attr('href', checkboxValue);

        var options = {
            closeClickOutside: false,
            smallBtn: false,
            infobar: false,
            buttons: false,
        };

        $.fancybox.open($container, options);
    });
}