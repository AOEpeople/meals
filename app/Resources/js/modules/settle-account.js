function settleAccount(that) {
    console.log('test1');
    console.log($(that));
    $('.button.settle-account').click(function () {
        console.log('test2');
        var $container = $('[data-account-settlement-confirmation]');
        var $profile = $(this).parent().children('#cash_profile').val();
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
