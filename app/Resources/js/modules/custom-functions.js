function toggleArrayItem(a, v) {
    var i = a.indexOf(v);
    if (i === -1)
        a.push(v);
    else
        a.splice(i,1);
}

Mealz.prototype.copyToClipboard = function() {
    'use strict';

    var that = this;

    // click events
    $('.guest-menu').on('click', function () {
        // Close all open overlays
        $('.guest-menu-link').removeClass('open');

        var dayId = $(this).attr('data-copytarget').split('-').pop();
        var guestMenuLinkInput = $(this).parent().find('.guest-menu-link input');

        var result = Mealz.prototype.loadGeneratedLink(dayId);
        if (that.isUrl(result)) {
            guestMenuLinkInput.attr("value", result);
            $(this).next().addClass('open');
            guestMenuLinkInput.select();
            document.execCommand('copy');
            guestMenuLinkInput.blur();
        } else {
            var $html = $(result);
            if ($html.length && $html.find('.login-form').length) {
                window.location.reload();
            }
        }
        return false;
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.guest-menu-link').length) {
            $('.guest-menu-link').removeClass('open');
        }
    });
};

Mealz.prototype.isUrl = function(s) {
    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    return regexp.test(s);
};

Mealz.prototype.loadGeneratedLink = function(dayId) {
    'use strict';

    var result = null;

    $.ajax({
        method: 'GET',
        url: '/app.php/menu/' + dayId + '/new-guest-invitation',
        async: false,
        success: function (data) {
            result = data;
        }
    });
    return result;
};