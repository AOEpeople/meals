Mealz.prototype.enableLightbox = function () {
    var $fancybox = $('.fancybox');
    $fancybox.attr('rel', 'gallery')
        .fancybox({
            padding: 40,
            openEffect: 'fade',
            helpers: {
                overlay: {closeClick: false}
            },
            closeClick: false,
            nextClick: false,
            mouseWheel: false,
            closeBtn: false,
            maxWidth: 400,
        });
    $fancybox.trigger('click');
    $(document ).ready(function() {
        $('.fancybox').find('.button').click(function(){
            $.fancybox.close();
        });
    });

};
