function settleAccount() {
    $('.button.balance-account').click(function () {
        var $container = $('[data-account-settlement-confirmation]');

        var options = {
            closeClickOutside: false,
            smallBtn: false,
            infobar: false,
            buttons: false,
        };

        $.fancybox.open($container, options);
    });
}