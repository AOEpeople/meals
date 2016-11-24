Mealz.prototype.enableLightbox = function () {
    var $fancybox = $('.fancybox');
    $fancybox.attr('rel', 'gallery')
        .fancybox({
            padding: 40,
            openEffect: 'fade',
            helpers: {
                title: null
            },
            closeClick: false,
            nextClick: false,
            mouseWheel: false,
            closeBtn: false,
            maxWidth: 400,
        });
    $fancybox.trigger('click');
    $('.fancybox-overlay').off('click');
    $('.fancybox > .button').click(function () {
        F = $.fancybox;
        if (F.isActive === true) {
            F.close();
        } else {
            $(this).close();
        }
    });
    $fancybox.off('click');
};