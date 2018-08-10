Mealz.prototype.enableConfirmSwapbox = function (checkboxValue) {
    var $container = $('[data-confirmSwapbox]');

    $('#swapLink').attr('href', checkboxValue);

    var options = {
        closeClickOutside: false,
        smallBtn: false,
        infobar: false,
        buttons: false,
    };

    $.fancybox.open($container, options);

};
