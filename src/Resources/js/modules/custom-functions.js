import AjaxErrorHandler from "./ajax-error-handler";

Mealz.prototype.toggleArrayItem = function (a, v) {
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
        var $that = $(this);

        // Close all open overlays
        $('.guest-menu-link').removeClass('open');

        var dayId = $that.attr('data-copytarget').split('-').pop();
        var guestMenuLinkInput = $that.parent().find('.guest-menu-link textarea');

        $.ajax({
            method: 'GET',
            url: '/menu/' + dayId + '/new-guest-invitation',
            success: function (result) {
                if (that.isUrl(result)) {
                    guestMenuLinkInput.val(result);
                    $that.next().addClass('open');
                    if (navigator.userAgent.match(/ipad|ipod|iphone/i)) {
                        var el = guestMenuLinkInput.get(0);
                        var editable = el.contentEditable;
                        var readOnly = el.readOnly;
                        el.contentEditable = true;
                        el.readOnly = false;
                        var range = document.createRange();
                        range.selectNodeContents(el);
                        var sel = window.getSelection();
                        sel.removeAllRanges();
                        sel.addRange(range);
                        el.setSelectionRange(0, 999999);
                        el.contentEditable = editable;
                        el.readOnly = readOnly;
                    } else {
                        guestMenuLinkInput.trigger('select');
                    }
                    // Clipboard copy only works in secure context, redirect http -> https
                    if (window.location.protocol === 'https:') {
                        navigator.clipboard.writeText(result);
                    } else {
                        console.warn('Clipboard access from insecure (HTTP) context is prohibited. Reload page with HTTPS and try again.');
                    }
                    guestMenuLinkInput.trigger('blur');
                } else {
                    var $html = $(result);
                    if ($html.length && $html.find('.login-form').length) {
                        window.location.reload();
                    }
                }
                return false;
            },
            error: function(jqXHR){
                AjaxErrorHandler.handleError(jqXHR);
            }
        });
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
