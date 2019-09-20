function settleAccount() {
    $('a.settle-account').click(function () {
        var $container = $('[data-account-settlement-confirmation]');
        var $profile = $(this).data('profile');
        var $continueButton = $('.button.account-settlement-confirmation-continue');

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
