import Switchery from 'switchery.js';

Mealz.prototype.applyCheckboxClasses = function ($checkbox) {
    var $checkboxWrapper = $checkbox.closest('.' + this.checkboxWrapperClass);

    // if I'm in Guest Invitation
    if($('.meal-guest').length && $checkboxWrapper.hasClass('disabled')){
        $checkbox.closest('input').attr('disabled', 'disabled');
    } else {
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

};

Mealz.prototype.styleCheckboxes = function () {
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

    $('.' + this.checkboxWrapperClass + ' input').on('click', function(e) {
        e.stopPropagation();
    });

    // Handle change event on checkboxes
    this.$participationCheckboxes.on('change', function() {
        that.toggleParticipation($(this));
    });

    // Handle change event on checkboxes
    this.$guestParticipationCheckboxes.on('change', function() {
        that.applyCheckboxClasses($(this));
        that.toggleGuestParticipation($(this));
    });
};

Mealz.prototype.styleSelects = function () {
    this.$selects.wrap('<div class="' + this.selectWrapperClass + '"></div>');
};

Mealz.prototype.applySwitcheryStates = function () {

    // disable all day checkboxes in disabled week
    for (var i = 0; i < this.weekDaySwitchery.length; i++) {

        // if enable is called on a already enabled switchery element, you can't switch
        // its status anymore by clicking (see https://github.com/abpetkov/switchery/issues/103)
        var weekDayDisabled = this.weekDaySwitchery[i].isDisabled();

        if (weekDayDisabled === true) {
            this.weekDaySwitchery[i].enable();
        } else if (weekDayDisabled === false) {
            this.weekDaySwitchery[i].disable();
        } else {
            this.weekDaySwitchery[i].disable();
        }
    }
};

Mealz.prototype.applyDropdownStatesByWeekState = function () {
    var that = this;

    $.each(this.$weekDayCheckboxes, function (i, e) {
        if ($(e).parent().parent().find('.week-day-action .js-switch').prop('checked') === true) {
            that.applyDropdownStates(e);
        }
    });
};

Mealz.prototype.applyDropdownStates = function (e) {
    $(e).parent().siblings(this.mealRowsWrapperClassSelector).toggleClass('disabled');
};

Mealz.prototype.initButtonHandling = function () {
    var that = this;

    // click handling for add button
    this.$profileAdd.on('click', function() {
        that.addProfile($(this));
    });
};
