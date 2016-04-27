var Mealz = function () {
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.$checkboxes = $('input.checkbox, input[type="checkbox"]');
    this.selectWrapperClass = 'select-wrapper';
    this.$selects = $("select");
    this.$body = $('body');
};

Mealz.prototype.applyCheckboxClasses = function ($checkbox) {
    var that = this;

    var $checkboxWrapper = $checkbox.closest('.' + that.checkboxWrapperClass);

    $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
    $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
};

Mealz.prototype.styleCheckboxes = function() {
    var that = this;

    // Check checkbox states
    this.$checkboxes.each(function(idx, checkbox) {
        var $checkbox = $(checkbox);
        that.applyCheckboxClasses($checkbox);
    });

    // Handle click event on checkbox representer
    this.$body.on('click', '.' + this.checkboxWrapperClass, function() {
        var $checkbox = $(this).find('input');
        $checkbox.trigger('click');
    });

    // Handle change event on checkboxes
    this.$checkboxes.on('change', function() {
        that.toggleParticipation($(this));
    });
};

Mealz.prototype.styleSelects = function() {
    this.$selects.wrap('<div class="' + this.selectWrapperClass + '"></div>');
};

Mealz.prototype.toggleParticipation = function ($checkbox) {
    var that = this;
    var $participantsCount = $checkbox.closest('.meal-row').find('.participants-count');
    var url = $checkbox.attr('value');

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $checkbox.attr('value', data.url);
            $participantsCount.fadeOut('fast', function () {
                that.applyCheckboxClasses($checkbox);
                $participantsCount.text(data.participantsCount);
                $participantsCount.fadeIn('fast');
            });
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

Mealz.prototype.loadDishForm = function ($element) {
    var url = $element.attr('href');
    var $dishForm = $('.dish-form');
    var animationDuration = 150;

    if($element.hasClass('dish-create') && $dishForm.is(':visible')) {
        $dishForm.slideUp(animationDuration);
        return false;
    }

    if ($element.hasClass('dish-create')) {
        $dishForm.addClass('form-dish-create');
    } else {
        $dishForm.removeClass('form-dish-create');
    }

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $dishForm.html(data);
            new Mealz().styleSelects();
            $dishForm.slideDown(animationDuration);
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

$(document).ready(function() {

    var mealz = new Mealz();
    mealz.styleCheckboxes();
    mealz.styleSelects();

    $('.hamburger').on('click', function() {
        $(this).toggleClass('is-active');
        $('.header-content').toggleClass('is-open');
    });

    $('.dish-load-form').on('click', function(e) {
        e.preventDefault();
        mealz.loadDishForm($(this));
    });
});
