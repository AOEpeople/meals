Mealz.prototype.initDishSelection = function () {
    var that = this;

    $('.meal-row .dishes').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.selectDish($(this), e);
    });

    $('.variation-checkbox').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.selectVariation($(this));
    });

    // Hiding select-box and limit-box if click anywhere else
    $('.meal-form, .meal-select-box').mouseup(function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.hideVariationSelectBox(e);
        if (!$('.limit-box').ismouseover()) {
            $('.limit-box').hide();
            $('.limit-box').children().remove();
        }

    });

    $('.meal-select-variations .button').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.hideVariationSelectBox(e);
    });

    $('.variation-button').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).next().toggle();
    });

    $('.remove-meal').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.clearDishSelection($(this));
        // disable remove icon
        $(this).attr('style', 'display: none;');
    });

    $('.meal-select').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.variation-checkbox.checked', this.prev).closest('li.dishes').find('.variation-button:first').click();
        $(this).prev().toggle();
    });

    $('.limit-icon').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var selectedDay = $(this).parent();
        var mealCount = 0;
        selectedDay.children('.meal-rows-wrapper').children('.meal-row').each(function(){
            if($(this).attr('data-attribute-selected-variations').length > 0){
                mealCount+= JSON.parse($(this).attr('data-attribute-selected-variations')).length;
                //mealCount+= $(this).attr('data-attribute-selected-variations').size();
            } else {
                mealCount++;
            }
        });

        selectedDay.children('.limit-box').append('<p>Limit</p>');
        for(mealCount; mealCount > 0; mealCount--){
            selectedDay.children('.limit-box').append('<span class="limit-input" contentEditable=true></span>');
        }
        selectedDay.children('.limit-box').append('<a href="#" class="limit-box-save button small" onclick="limitBoxSaveClick()">save</a>');
        selectedDay.children('.limit-box').show();
    });

};

limitBoxSaveClick = function(){
    var thisDay = $('.limit-box').filter(":visible").parent();
    thisDay.find('.limit-input').each(function(i){
        thisDay.find('.participation-limit').eq(i).val($(this).text());
    });
};

Mealz.prototype.selectDish = function ($element, e) {
    var $mealRow = $element.closest('.meal-row');
    var dishId = $element.data('attribute-id');

    // add remove icon
    $mealRow.find('.remove-meal').attr('style', 'display: block;');

    if ($element.attr('data-attribute-parent') !== 'true') {
        // add remove icon
        $mealRow.find('.remove-meal').attr('style', 'display: block;');

        $mealRow.data('attribute-selected-dish', dishId);
        $mealRow.find('.meal-label').text($element.find('.dish-title').html());
        $mealRow.attr('data-attribute-selected-variations', '');
        $mealRow.find('.variation-checkbox').removeClass('checked');
        this.clearAllFormElements($mealRow);
        this.createMealFormElement($mealRow, dishId);
        this.hideVariationSelectBox(e);
    }
};

Mealz.prototype.selectVariation = function ($element) {
    var parentId = $element.closest('.dishes').attr('data-attribute-id');
    var variationId = $element.parent('.variation').data('attribute-id');

    var $mealRow = $element.closest('.meal-row');
    var selectedDish = $mealRow.data('attribute-selected-dish');
    var $input = $mealRow.children('.meal-selected').first();
    var variations = [];
    var that = this;

    // If data-attribute-selected-dish change, delete variations
    if ($mealRow.attr('data-attribute-selected-dish') !== parentId) {
        $mealRow.attr('data-attribute-selected-variations', '');
        $mealRow.find('.variation-checkbox.checked').removeClass('checked');
    }

    var previousSelectedVariations = $mealRow.attr('data-attribute-selected-variations');

    // If data-attribute selected-variations is defined and not empty
    if (previousSelectedVariations.length > 0) {
        previousSelectedVariations = JSON.parse(previousSelectedVariations);
        variations = previousSelectedVariations;
    }

    toggleArrayItem(variations, variationId);

    // Set meal row data attributes and dropdown label
    $mealRow.attr('data-attribute-selected-variations', JSON.stringify(variations));
    this.setDropdownLabelForSelectedVariations($mealRow, parentId, variations);

    $mealRow.attr('data-attribute-selected-dish', parentId);

    // add remove icon
    $mealRow.find('.remove-meal').attr('style', 'display: block;');

    // If checkbox wasn't checked before
    if ($element.hasClass('checked') === false) {
        // If this is the first selected variation
        if (variations.length === 1) {
            that.clearAllFormElements($mealRow);
            that.createMealFormElement($mealRow, variationId);
            // Else (If other variations were selected before)
        } else {
            // add remove icon
            $mealRow.find('.remove-meal').attr('style', 'display: block;');
            that.createMealFormElement($mealRow, variationId);
        }
    } else {
        // If there are still variations selected
        if (variations.length > 0) {
            // Find and remove input field with the same value like the id of clicked variation
            $mealRow.find('.meal-selected').each(function () {
                if ($(this).find('input:first').val() === variationId) {
                    that.deleteSingleSelection($(this));
                }
            });
            // Else (If no variation, and therefore no dish, is selected anymore)
        } else {
            // disable remove icon
            $mealRow.find('.remove-meal').attr('style', 'display: none;');

            // Remove every other input except first
            $mealRow.attr('data-attribute-selected-dish', '');
            that.clearAllFormElements($mealRow);
        }
    }
    $element.toggleClass('checked');
};

Mealz.prototype.clearDishSelection = function ($element) {
    var $mealRow = $element.closest('.meal-row');

    // Remove data attribute values from meal-row
    $mealRow.attr('data-attribute-selected-dish', '');
    $mealRow.attr('data-attribute-selected-variations', '');

    // Clear dropdown text
    $element.prev('.meal-label').empty();

    // Remove value (dish id) from default input field and remove others
    this.clearAllFormElements($mealRow);

    //Remove tick from variation checkboxes
    $mealRow.find('.variation-checkbox.checked').removeClass('checked');
};

Mealz.prototype.setDropdownLabelForSelectedVariations = function (mealRow, parentId, variations) {
    var mealRowLabel = mealRow.find('.meal-label');
    if (variations.length > 0) {
        var $parentDish = $('li.dishes[data-attribute-id=' + parentId + ']');
        var parentLabel = $parentDish.find('.dish-title').html();
        var variationsLabel = '';

        for (var i = 0; i < variations.length; i++) {
            variationsLabel += (i > 0) ? ', ' : '';
            variationsLabel += $parentDish.find('.variation[data-attribute-id=' + variations[i] + '] span').html();
        }
        mealRowLabel.text(parentLabel + ' - ' + variationsLabel);
    } else {
        mealRowLabel.empty();
    }
};

Mealz.prototype.hideVariationSelectBox = function (e) {
    var container = '';
    if ($(e.currentTarget).hasClass('meal-select-box')) {
        container = $('.meal-select-variations');
    } else {
        container = $('.meal-select-box');
    }

    // if the target of the click isn't the container nor a descendant of the container
    if (!container.is(e.target) && container.has(e.target).length === 0 ||
        ($(e.currentTarget).attr('data-attribute-parent') != 'true' && $(e.currentTarget).is('[data-attribute-parent]'))) {

        container.hide('fast');
    }

    /* if SelectionBox has no checked Variations - close it */
    if ($(e.target).hasClass('small') === true) {
        var thisMealSelectBox = $(e.target).closest('.meal-select-variations');
        if (thisMealSelectBox.find('.checked').length === 0) {
            thisMealSelectBox.children('.error').show();
        } else if(thisMealSelectBox.find('.checked').length > 0) {
            thisMealSelectBox.children('.error').hide();
            thisMealSelectBox.hide();
            container.hide('fast');
        }
    }
};

Mealz.prototype.deleteSingleSelection = function ($element) {
    if ($element.hasClass('meal-persisted') === true) {
        $element.find('input:first').val('');
    } else {
        $element.remove();
    }
};

Mealz.prototype.clearAllFormElements = function ($mealRow) {
    var that = this;
    $mealRow.find('.meal-selected').each(function () {
        that.deleteSingleSelection($(this));
    });
};

Mealz.prototype.createMealFormElement = function ($mealRow, dishId) {
    // Get prototype and day id and retrieve prototype form from data-prototype attribute
    if (this.prototypeFormId === undefined) {
        this.prototypeFormId = $mealRow.closest('.day').find('.meal-selected').length;
    }
    this.prototypeFormId += 1;
    var day = $mealRow.children('.meal-selected:first').find('input:last').val();
    var prototypeForm = $mealRow.parent('.meal-rows-wrapper').data('prototype');

    // Set prototype, day and dish id in prototype form and append form element to related meal row
    prototypeForm = prototypeForm.replace(/__name__/g, this.prototypeFormId);
    var $prototypeFormElement = $(prototypeForm).appendTo($mealRow);
    $prototypeFormElement.addClass('meal-selected');
    var $prototypeFormElementInputs = $prototypeFormElement.find('input');
    $prototypeFormElementInputs.last().val(day);
    $prototypeFormElementInputs.first().val(dishId);
};
