function toggleArrayItem(a, v) {
    var i = a.indexOf(v);
    if (i === -1)
        a.push(v);
    else
        a.splice(i,1);
}

Mealz.prototype.copyToClipboard = function() {
    'use strict';

    // click events
    $('.guest-menu').on('click', function () {
        // Close all open overlays
        $('.guest-menu-link').removeClass('open');

        var dayId = $(this).attr('data-copytarget').split('-').pop();
        var guestMenuLinkInput = $(this).parent().find('.guest-menu-link input');

        Mealz.prototype.loadGeneratedLink(dayId, guestMenuLinkInput);
        $(this).next().addClass('open');
        guestMenuLinkInput.select();
        document.execCommand('copy');
        guestMenuLinkInput.blur();
        return false;
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.guest-menu-link').length) {
            $('.guest-menu-link').removeClass('open');
        }
    });
};

Mealz.prototype.loadGeneratedLink = function(dayId,copyTo) {
    $.ajax({
        method: 'GET',
        url: '/app.php/menu/' + dayId + '/new-guest-invitation',
        async: false,
        success: function (data) {
            copyTo.attr('value', data);
        }
    });
};