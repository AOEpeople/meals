Mealz.prototype.enableLightbox = function () {
    var $container = $('[data-fancybox]');

    var $options = {
        closeClickOutside: false,
        smallBtn : false,
        infobar : false,
        buttons : false,
    };

    $.fancybox.open($container, $options);

};
