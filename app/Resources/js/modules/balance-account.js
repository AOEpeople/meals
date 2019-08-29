function balanceAccount() {
    $('.button.balance-account').click(function () {

        var $container = $('[data-balance-account]');
        var $profile = $(this).parent().children('#cash_profile').val();
        var $continueButton = $('.balance-account-item-continue');

        $continueButton.attr('href', $continueButton.attr('href').replace('_', $profile));

        var options = {
            closeClickOutside: false,
            smallBtn: false,
            infobar: false,
            buttons: false,
        };

        $.fancybox.open($container, options);
    });
}
