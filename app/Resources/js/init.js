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
    var $participantsCount = $checkbox.closest('.wrapper-meal-actions').find('.participants-count');
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

function toggleArrayItem(a, v) {
    var i = a.indexOf(v);
    if (i === -1)
        a.push(v);
    else
        a.splice(i,1);
}

function unique(list) {
    var result = [];
    $.each(list, function(i, e) {
        if ($.inArray(e, result) == -1) result.push(e);
    });
    return result;
}

function hideSelectBox(e) {
    var container = '';
    if($(e.currentTarget).hasClass('meal-select-box')) {
        container = $(".meal-select-variations");
    } else {
        container = $(".meal-select-box");
    }

    // if the target of the click isn't the container nor a descendant of the container
    if (!container.is(e.target) && container.has(e.target).length === 0 || $(e.currentTarget).hasClass('button') || ($(e.currentTarget).attr('data-attribute-parent') != 'true' && $(e.currentTarget).is('[data-attribute-parent]')))
    {
        container.hide('fast');
    }

    /* if SelectionBox has no checked Variations - close it */
    if ($(e.target).hasClass('small')){
        var thisMealSelectBox = $(e.target).closest('.meal-select-variations');
        if(thisMealSelectBox.find('.checked').length === 0){
            thisMealSelectBox.hide();
       }
    }

}

function setMealRowLabel(mealRow, parentId, variations) {
    var mealRowLabel = mealRow.find('.meal-label');
    if(variations.length > 0) {
        var $parentDish = $("li.dishes[data-attribute-id=" + parentId + "]");
        var parentLabel = $parentDish.find('.dish-title').html();
        var variationsLabel = '';

        for(var i = 0; i < variations.length; i++) {
            variationsLabel += (i > 0) ? ', ' : '';
            variationsLabel += $parentDish.find(".variation label[data-attribute-id=" + variations[i] + "]").html();
        }
        mealRowLabel.text(parentLabel + ' - ' + variationsLabel);
    } else {
        mealRowLabel.empty();
    }
}

Mealz.prototype.selectMeal = function () {
    var selectedDish = $('.meal-row .dishes');
    var variationCheckbox = $('.variation-checkbox');
    var variations = [];

    selectedDish.on('click', function (e) {
        var mealRow = $(this).closest('.meal-row');
        var dishId = $(e.currentTarget).data('attribute-id');

        if($(this).attr('data-attribute-parent') != 'true') {
            mealRow.attr('data-attribute-selected-dish', dishId);
            mealRow.find('.meal-label').text($(this).find('.dish-title').html());
            mealRow.attr('data-attribute-selected-variations', "");
            mealRow.find('.variation-checkbox').removeClass("checked");
            variations.length = 0;
            hideSelectBox(e);
        }
    });

    variationCheckbox.on('click', function (e) {
        var parentId = $(this).closest('.dishes').attr('data-attribute-id');
        var variationId = $(this).next().data('attribute-id');
        var mealRow = $(this).closest('.meal-row');

        if(parentId !== mealRow.attr('data-attribute-selected-dish') && variations.length > 0) {
            variations.length = 0;
        }

        if(!$(this).hasClass('checked')) {
            toggleArrayItem(variations, variationId);
        }
        /* if the checkbox was checked and we deselect it */
        else {
            var deselectedVariationId = $(this).next().attr('data-attribute-id');
            var selectedVariations = [];
            $(this).closest('.meal-row').find('.meal-selected').children().each(function(){
                if($(this).val() === deselectedVariationId) {
                    $(this).attr('value', '');

                    selectedVariations = JSON.parse($(this).closest('.meal-row').attr('data-attribute-selected-variations'));
                    selectedVariations.splice( $.inArray(deselectedVariationId, selectedVariations), 1 );
                    variations = selectedVariations;
                }
            });
        }

        variations = unique(variations);

        mealRow.attr('data-attribute-selected-dish', parentId);
        mealRow.attr('data-attribute-selected-variations', JSON.stringify(variations));

        setMealRowLabel(mealRow, parentId, variations);

        // Fill input fields
        e.preventDefault();
        e.stopPropagation();

        var thisVariation = $(this);

        /* if checkbox is not checked yet */
        if(!thisVariation.hasClass('checked')) {
            var $mealRow = thisVariation.closest('.meal-row');
            var $selectedDish = $mealRow.data('attribute-selected-dish');
            var $selectedVariations = $mealRow.attr('data-attribute-selected-variations');
            var $input = $mealRow.children('.meal-selected').first();

            if (!$selectedVariations || $selectedVariations == 'null') {
                $input.find('input').first().val($selectedDish);
            } else if (JSON.parse($selectedVariations).length === 1 ) {
                $selectedVariations = JSON.parse($selectedVariations);
                $input.find('input').first().val($selectedVariations[0]);
            } else {
                var bool = true;
                $mealRow.children('.meal-selected').find('input').each(function(){
                    if ($(this).first().val() == ''){
                        $(this).first().val(thisVariation.next().attr('data-attribute-id'));
                        bool = false;
                    }
                });
                if(bool){
                    $selectedVariations = JSON.parse($selectedVariations);
                    // Retrieve prototype form from data-prototype attribute
                    var prototypeForm = $mealRow.data('prototype');

                    // Get day and meal id for prototype
                    var day = $mealRow.children('.meal-selected').first().find('input').last().val();
                    var dish = $selectedVariations[$selectedVariations.length - 1];
                    var prototypeFormId = $mealRow.closest('.day').find('.meal-selected').length;

                    // Set meal id in prototype form and append form element to other form elements
                    prototypeForm = prototypeForm.replace(/__name__/g, prototypeFormId);
                    var $prototypeFormElement = $(prototypeForm).appendTo($mealRow);
                    $prototypeFormElement.addClass('meal-selected');

                    // Set day and dish for prototype form element
                    // var $prototypeFormElement = $mealRow.children('.meal-selected').last();
                    $prototypeFormElement.find('input').last().val(day);
                    $prototypeFormElement.find('input').first().val(dish);
                }
            }
        }


        $(this).toggleClass('checked');
    });



};


$(document).ready(function() {
    var mealz = new Mealz();
    mealz.styleCheckboxes();
    mealz.styleSelects();
    mealz.selectMeal();

    /* hiding select-box if click anywhere else */
    $('.meal-form, .meal-select-box').mouseup(function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideSelectBox(e);
    });

    $('.meal-select-variations .button').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        hideSelectBox(e);
    });

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

    $('.meal-select').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).prev().toggle();
    });

    $('.variation-button').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).next().toggle();
    });

    /* setting meal-select box text */
    $('.meal-select-box').each(function(){
        var $that = $(this);
        var $mealRow = $that.closest('.meal-row');
        var $selectedDish = $mealRow.data('attribute-selected-dish');
        var htmlButton = '<a class="remove-meal" href=\"#\"><span class=\"glyphicon glyphicon-remove\"></span></a>';

        if ($selectedDish) {
            $(htmlButton).appendTo($mealRow.find('.meal-select').first());
        }

    });

    $('.remove-meal').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $that = $(this);
        var $mealRow = $that.closest('.meal-row');

        // Remove data attribute values from meal-row
        $mealRow.attr('data-attribute-selected-dish', 'null');
        $mealRow.attr('data-attribute-selected-variations', 'null');

        // Clear dropdown text
        $that.prev('.meal-label').empty();

        // Remove value (dish id) from default input field and remove others
        $mealRow.find('.meal-selected').slice(1).remove();
        $mealRow.find('.meal-selected').first().find('input').first().attr('value', '');

        //Remove tick from variation checkboxes
        $checkedCheckboxes = $mealRow.find('.variation-checkbox.checked');
        $checkedCheckboxes.removeClass('checked');
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
