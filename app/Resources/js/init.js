var Mealz = function () {
    this.checkboxWrapperClass = 'checkbox-wrapper';
    this.hiddenClass = 'hidden';
    this.weekCheckbox = $('.meal-form .week-disable input[type="checkbox"]')[0];
    this.$weekDayCheckboxes = $('.meal-form .week-day-action input[type="checkbox"]');
    this.$participationCheckboxes = $('.meals-list input.checkbox, .meals-list input[type = "checkbox"]');
    this.$iconCells = $('.icon-cell');
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
            if (data.redirect) {
                window.location.replace(data.redirect);
            }
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

Mealz.prototype.toggleParticipationAdmin = function ($element) {
    var url = $element.attr('href');

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $element.attr('href', data.url);
            $element.parent().toggleClass('participating');
            $element.text(data.actionText);
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
    var $editFormWrapper = $('.edit-form:visible');
    var $elementParentRow = $element.closest('.table-row');
    var $ajaxRow;

    if ($createForm.is(':visible')) {
        $createForm.slideUp(animationDuration);
        if ($element.hasClass('load-create-form')) {
            return;
        }
    } else if ($element.hasClass('load-create-form') && $element.hasClass('loaded')){
        $createForm.slideDown(animationDuration);
        $editFormWrapper.find('form').slideUp(animationDuration, function () {
            $editFormWrapper.hide();
        });
        return;
    }

    if ($editFormWrapper.length > 0) {
        $ajaxRow = $elementParentRow.next('.table-row-form');

        var ajaxRowVisible = ($ajaxRow.length > 0 && $ajaxRow.is(':visible')) ? true : false;

        $editFormWrapper.find('form').slideUp(animationDuration, function() {
            $editFormWrapper.hide();
        });

        if (!ajaxRowVisible && $element.hasClass('load-edit-form') && $element.hasClass('loaded')) {
            $ajaxRow.show();
            $ajaxRow.find('form').slideDown(animationDuration);
            return;
        } else if (ajaxRowVisible) {
            return;
        }
    } else if ($element.hasClass('load-edit-form') && $element.hasClass('loaded')){
        $ajaxRow = $elementParentRow.next('.table-row-form');
        $ajaxRow.show();
        $ajaxRow.find('form').slideDown(animationDuration);
        return;
    }

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            var $wrapperForm;

            if ($element.hasClass('load-create-form')) {
                $createForm.html(data);
                $createForm.slideDown(animationDuration);
                $wrapperForm = $createForm;
            } else {
                $wrapperForm = $(data).insertAfter($elementParentRow);
                $wrapperForm.find('form').slideDown(animationDuration);
            }

            // Style selects
            $wrapperForm.find('select').wrap('<div class="' + that.selectWrapperClass + '"></div>');

            $element.addClass('loaded');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

Mealz.prototype.loadAjaxFormPayment = function($element) {
    var that = this;
    var url = $element.attr('href');
    var $elementParent = $element.parent();
    var $form = $elementParent.find('form');

    if ($form.length !== 0) {
        that.$iconCells.find('form').addClass(that.hiddenClass);
        $form.toggleClass(this.hiddenClass);
        if (!$form.hasClass(this.hiddenClass)) {
            $form.find("input[type=text]").focus();
        }
        return;
    }

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            that.$iconCells.find('form').addClass(that.hiddenClass);
            $element.after(data);
            $elementParent.find('form').find('input[type=text]').focus();
            $elementParent.children('form').on('click', function(e){
                e.stopPropagation();
            });
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

    $('.print-participations .meal-participation a').on('click', function(e) {
        e.preventDefault();
        mealz.toggleParticipationAdmin($(this));
    });

    $('.load-payment-form').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        mealz.loadAjaxFormPayment($(this));
    });

    $('body').on('click', function() {
        mealz.$iconCells.find('form').addClass(mealz.hiddenClass);
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

    $('.fancybox').attr('rel', 'gallery')
        .fancybox({
            padding : 40,
            openEffect : 'fade',
            helpers : {
                title : null
            },
            closeClick: false,
            nextClick: false,
            mouseWheel: false,
            closeBtn: false,
            maxWidth: 400,
        });
    $('.fancybox').trigger('click');
    $('.fancybox-overlay').unbind('click');
    $('.fancybox > .button').click(function(){
        F = $.fancybox;
        if (F.isActive) {
            F.close();
        } else {
            $(this).close();
        }
    });
    $('.fancybox').unbind('click');
});
