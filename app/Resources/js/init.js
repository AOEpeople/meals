Array.prototype.remove = function () {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = $.inArray(what, this)) != -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

var Mealz = function () {
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.$checkboxes = $('input.checkbox, input[type="checkbox"]');
    this.$body = $('body');
};

Mealz.prototype.styleCheckboxes = function() {
    var that = this;

    this.$checkboxes.wrap('<div class="' + this.checkboxWrapperClass + '"></div>');

    // Helper function to apply certain classes
    var applyCheckboxClasses = function($checkbox) {
        var $checkboxWrapper = $checkbox.closest('.' + that.checkboxWrapperClass);

        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    };

    // Check checkbox states
    this.$checkboxes.each(function(idx, checkbox) {
        var $checkbox = $(checkbox);
        applyCheckboxClasses($checkbox);
    });

    // Handle click event on checkbox representer
    this.$body.on('click', '.' + this.checkboxWrapperClass, function() {
        var $checkbox = $(this).find('input');
        $checkbox.trigger('click');
    });

    // Handle change event on checkboxes
    this.$checkboxes.on('change', function() {
        var $checkbox = $(this);
        applyCheckboxClasses($checkbox);
    });
};

Mealz.prototype.toggleParticipation = function ($checkbox) {
    var $participantsCount = $checkbox.closest('.meal-row').find('.participants-count');
    var url = $checkbox.attr('value');
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $checkbox.attr('value', data.url)
            $participantsCount.fadeOut('fast', function () {
                $participantsCount.text(data.participantsCount);
                $participantsCount.fadeIn('fast');
            });
        },
        error: function (xhr, statusText, errorThrown) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

$(document).ready(function() {

    var mealz = new Mealz();
    mealz.styleCheckboxes();

    $('.hamburger').on('click', function() {
        $(this).toggleClass('is-active');
        $('.header-right').toggleClass('is-open');
    });

    $('.participation-checkbox').on('click', function () {
        mealz.toggleParticipation($(this));
    });
});
