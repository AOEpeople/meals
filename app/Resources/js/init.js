var Mealz = function () {
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.weekCheckbox = $('.meal-form .week-disable input[type="checkbox"]')[0];
    this.$weekDayCheckboxes = $('.meal-form .week-day-action input[type="checkbox"]');
    this.$participationCheckboxes = $('.meals-list input.checkbox, .meals-list input[type = "checkbox"]');
    this.selectWrapperClass = 'select-wrapper';
    this.mealRowsWrapperClassSelector = '.meal-rows-wrapper';
    this.$selects = $("select");
    this.$body = $('body');
};

Mealz.prototype.applyCheckboxClasses = function ($checkbox) {
    var $checkboxWrapper = $checkbox.closest('.' + this.checkboxWrapperClass);

    $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
    $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
};

Mealz.prototype.applySwitcheryStates = function () {
    // disable all day checkboxes in disabled week
    for (var i = 0; i < this.weekDaySwitchery.length; i++) {
        // if enable is called on a already enabled switchery element, you can't switch
        // its status anymore by clicking (see https://github.com/abpetkov/switchery/issues/103)
        var weekDayDisabled = this.weekDaySwitchery[i].isDisabled();

        if (weekDayDisabled){
            this.weekDaySwitchery[i].enable();
        } else if(!weekDayDisabled) {
            this.weekDaySwitchery[i].disable();
        }
    }
};

Mealz.prototype.applyDropdownStatesByWeekState = function() {
    var that = this;

    if (this.weekCheckbox.checked) {
        $.each(this.$weekDayCheckboxes, function (i, e) {
            that.applyDropdownStates(e);
        });
    } else {
        $('select').prop('disabled', true);
    }
};

Mealz.prototype.applyDropdownStates = function (e) {
    var selects = $(e).parent().siblings(this.mealRowsWrapperClassSelector).find('select');
    selects.prop('disabled', !e.checked);
};

Mealz.prototype.styleCheckboxes = function() {
    var that = this;

    // Week detail view
    if (this.weekCheckbox && this.$weekDayCheckboxes) {
        // Enable switchery for week days
        this.weekDaySwitchery = [];
        this.$weekDayCheckboxes.each(function (idx, checkbox) {
            that.weekDaySwitchery.push(new Switchery(checkbox));
        });

        // Enable switchery for week
        var weekSwitchery = new Switchery(this.weekCheckbox);
        weekSwitchery = $(weekSwitchery.switcher).detach();
        weekSwitchery.appendTo('.meal-form .headline-tool .switchery-placeholder');

        // Toggle day switcher and dropdown state on changed week state
        this.weekCheckbox.onchange = function () {
            that.applySwitcheryStates();
            that.applyDropdownStatesByWeekState();
        };

        // Toggle dropdown state on changed day state
        this.$weekDayCheckboxes.on('change', function () {
            that.applyDropdownStates(this);
        });

        // Enable checkboxes before submit, otherwise they will be set to false
        $('.meal-form .week-form > form').on('submit', function () {
            for (var i = 0; i < that.weekDaySwitchery.length; i++) {
                var current = that.weekDaySwitchery[i];
                if (current.isDisabled()) {
                    current.enable();
                    current.switcher.style.opacity = 0.5;
                }
            }
        });
    }

    // Check checkbox states
    this.$participationCheckboxes.each(function(idx, checkbox) {
        var $checkbox = $(checkbox);
        that.applyCheckboxClasses($checkbox);
    });

    // Handle click event on checkbox representer
    this.$body.on('click', '.' + this.checkboxWrapperClass, function() {
        var $checkbox = $(this).find('input');
        $checkbox.trigger('click');
    });

    // Handle change event on checkboxes
    this.$participationCheckboxes.on('change', function() {
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

Mealz.prototype.loadAjaxForm = function ($element) {
    var that = this;

    var url = $element.attr('href');
    var animationDuration = 150;

    var $createForm = $('.create-form');
    var $editFormWrapper = $('.edit-form');
    var $editForm = $editFormWrapper.find('form');

    if ($element.hasClass('load-create-form') && $createForm.is(':visible')) {
        $createForm.slideUp(animationDuration);
        return false;
    } else if ($element.hasClass('load-create-form')) {
        $editForm.slideUp(animationDuration, function () {
            $editFormWrapper.remove();
        });
    } else if ($element.hasClass('load-edit-form') && $editForm.length > 0) {
        $editForm.slideUp(animationDuration, function () {
            $editFormWrapper.remove();
        });
    } else if ($element.hasClass('load-edit-form')) {
        $createForm.slideUp(animationDuration);
    }

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $(".table-row-form").remove();
            if ($element.hasClass('load-create-form')) {
                $createForm.html(data);
                $createForm.slideDown(animationDuration);
            } else {
                var $parentRow = $element.closest('.table-row');
                $parentRow.after(data);
                $('.edit-form form').slideDown(animationDuration);
            }
            that.$selects = $("select");
            that.styleSelects();
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

    $('.load-ajax-form').on('click', function(e) {
        e.preventDefault();
        mealz.loadAjaxForm($(this));
    });

    $('.table-sortable').DataTable({
        'aaSorting': [], // Disable initial sort
        paging: false,
        searching: false,
        info: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false
        }]
    });
});
