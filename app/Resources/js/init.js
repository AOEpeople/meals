var Mealz = function () {
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.weekCheckbox = $('.meal-form .week-disable input[type="checkbox"]')[0];
    this.$weekDayCheckboxes = $('.meal-form .week-day-action input[type="checkbox"]');
    this.$participationCheckboxes = $('.meals-list input.checkbox, .meals-list input[type = "checkbox"]');
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

Mealz.prototype.applySwitcheryStates = function (call) {
    var that = this;

    //// disable all days in disabled week
    //that.$weekDayCheckboxes.each(function (idx, checkbox) {
    //    console.log(that.weekCheckbox.checked);
    //    console.log(checkbox.checked);
    //    if (that.weekCheckbox.checked !== checkbox.checked) {
    //        checkbox.click();
    //        console.log(checkbox.checked);
    //    }
    //});

    // disable all day checkboxes in disabled week
    if (typeof(call) === 'undefined') {
        call = that.weekCheckbox.checked ? 'enable' : 'disable';
    }

    for (var i = 0; i < that.weekDaySwitchery.length; i++) {
        // if enable is called on a already enabled switchery element, you can't switch
        // its status anymore by clicking (see https://github.com/abpetkov/switchery/issues/103)
        var weekDaySwitcheryIsDisabled = that.weekDaySwitchery[i].isDisabled();
        if (weekDaySwitcheryIsDisabled && call === 'enable' ||
            !weekDaySwitcheryIsDisabled && call === 'disable') {
            that.weekDaySwitchery[i][call]();
        }
    }
};

Mealz.prototype.styleCheckboxes = function() {
    var that = this;

    if (this.weekCheckbox && this.$weekDayCheckboxes) {
        // Enable switchery for week days
        that.weekDaySwitchery = [];
        this.$weekDayCheckboxes.each(function (idx, checkbox) {
            that.weekDaySwitchery.push(new Switchery(checkbox));
        });

        // Enable switchery for week
        new Switchery(this.weekCheckbox);
        var weekSwitchery = $('.meal-form .week-disable > .switchery').detach();
        weekSwitchery.appendTo('.meal-form .headline-tool');

        that.applySwitcheryStates();
        this.weekCheckbox.onchange = function () {
            that.applySwitcheryStates();
        };

        // enable checkboxes before submit, otherwise they will be set to false
        $('.meal-form .week-form > form').on('submit', function () {
            that.applySwitcheryStates('enable');
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

Mealz.prototype.loadDishForm = function ($element) {
    var url = $element.attr('href');
    var $dishForm = $('.dish-form');
    var animationDuration = 150;

    if($element.hasClass('dish-create') && $dishForm.is(':visible') && $dishForm.hasClass('form-dish-create')) {
        $dishForm.slideUp(animationDuration);
        return false;
    }

    $dishForm.toggleClass('form-dish-create', $element.hasClass('dish-create'));

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $dishForm.html(data);
            new Mealz().styleSelects();
            if(!$dishForm.is(':visible')) {
                $dishForm.slideDown(animationDuration);
            }
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

    $('#dish-table').DataTable({
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
