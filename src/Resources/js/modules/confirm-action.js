Mealz.prototype.confirmAction = function (linkSelector, hiddenContainer, continueButtonSelector) {
    $(linkSelector).on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        let $container = $('['+hiddenContainer+']');
        let $profile = $(this).data('profile');
        let $continueButton = $(continueButtonSelector);

        $continueButton.attr('href', $continueButton.attr('href').replace('_', $profile));

        let options = {
            closeClickOutside: false,
            smallBtn: false,
            infobar: false,
            buttons: false,
        };

        $.fancybox.open($container, options);
    });
}