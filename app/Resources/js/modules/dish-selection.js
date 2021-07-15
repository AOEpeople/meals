Mealz.prototype.initDishSelection = function () {
    var that = this;


    $('.day .limit-icon').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $this = $(this);
        if($this.next('.limit-box').is(':visible')) {
            limitBoxSaveClick();
            return;
        } else {
            $this.parent('.day').siblings().find('.limit-box').each(function () {
                limitBoxSaveClick();
            });
        }
        var selectedDay = $(this).parent();
        var limitBoxSelectedDay= selectedDay.children('.limit-box');
        // start to build my limit-box
        limitBoxSelectedDay.append('<p>Limit</p>');
        // for each meal selected a day we need a new input row
        selectedDay.children('.meal-rows-wrapper').children('.meal-row').each(function(){
            // but we have to distinguish between Meal and Variation
            var thisMealRow = $(this);
            if(thisMealRow.is('[data-attribute-selected-dish]') && thisMealRow.is('[data-attribute-selected-variations]') &&
                thisMealRow.attr('data-attribute-selected-variations').length > 0){
                // if a variation is set, we need to find out the name for all variations selected
                var mainDishId = parseInt(thisMealRow.attr('data-attribute-selected-dish'));
                var mainDishTitle = thisMealRow.find('.dishes[data-attribute-id="'+mainDishId+'"] span.dish-title').text();
                $.each(JSON.parse(thisMealRow.attr('data-attribute-selected-variations')), function(key, value){
                    var mealName = thisMealRow.find('.variation').filter('[data-attribute-id='+parseInt(value)+']').find('span').text();
                    limitBoxSelectedDay.append('<div class="limit-box-meal"><label>'+mainDishTitle+' - '+mealName+'</label><span class="limit-input" contentEditable=true></span></div>');
                });
            } else {
                // if it's just a meal, we just need to find the meal Name once
                var mealName = "";
                // if the meal is not selected now, we have do catch error cases
                if(thisMealRow.attr('data-attribute-selected-dish') !== ""){
                    mealName = thisMealRow.find('.dishes').filter('[data-attribute-id=' + thisMealRow.attr('data-attribute-selected-dish') + ']').children('span').text();
                }
                // if the mealname is not set hard in form (if you select it the first time)
                // we have to take it from the meal-select span
                if (mealName.length === 0){
                    mealName = thisMealRow.children('.meal-select').children('span').text();
                }

                limitBoxSelectedDay.append('<div class="limit-box-meal"><label>'+mealName+'</label><span class="limit-input" contentEditable=true></span></div>');
            }
        });

        // prefill all LimitValues
        selectedDay.find('.participation-limit').each(function(i){
            // but only if there is some value
            if($(this).val() !== ""){
                $('.limit-input').eq(i).text(parseInt($(this).val()));
            } else {
                $(selectedDay).children('.limit-icon').removeClass('modified');
            }
        });

        // add Save Button
        limitBoxSelectedDay.append('<a href="#" class="limit-box-save button small" onclick="limitBoxSaveClick()">save</a>');
        limitBoxSelectedDay.show('fast');
    });

    $('.meal-select').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.variation-checkbox.checked', this.prev).closest('li.dishes').find('.variation-button:first').click();
        $(this).prev().toggle();
    });

    $('.meal-select .remove-meal').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.clearDishSelection($(this));
        // disable remove icon
        $(this).attr('style', 'display: none;');
    });

    $('.meal-select-box .dishes').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.selectDish($(this), e);
    });

    // Click on 'variations' button to open up variations selection
    $('.meal-select-box .dishes .variation-button').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $mealSelectVariations = $(this).next();
        var $dish = $mealSelectVariations.parent('.dishes');

        if ($mealSelectVariations.is(':visible')) {
            that.hideVariationSelectBox();
        } else {
            that.hideVariationSelectBox();
            $mealSelectVariations.show();
            $dish.addClass('active');
        }
    });

    $('.meal-select-box .dishes .meal-select-variations .variation-checkbox').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.selectVariation($(this));
    });

    // Click on save button in variations selection
    $('.meal-select-variations .button').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.hideSelectBox();
    });

    // Hiding meal-select-box and limit-box if click anywhere else
    $('.meal-form').click(function (e) {
        e.stopPropagation();
        that.hideSelectBox();
        var isOnSomeBox = false;
        var limitBox = $('.limit-box');
        limitBox.each(function(){
            if(!isOnSomeBox){
                isOnSomeBox = $(this).ismouseover();
            }
        });
        if(!isOnSomeBox){
            limitBox.hide();
            limitBox.children().remove();
        }
    });
};

window.limitBoxSaveClick = function(){
    var thisDay = $('.limit-box').filter(":visible").parent();
    var isOkayToClose = true;
    thisDay.find('.limit-input').each(function(i){
        if(Number.isInteger(parseInt($(this).text()))){
            thisDay.find('.participation-limit').eq(i).val(parseInt($(this).text()));
        } else if($(this).text() === "") {

        } else {
            $(this).css('border-color', 'red');
            isOkayToClose = false;
        }
    });
    if (isOkayToClose){
        $('.limit-box').hide('fast');
        $('.limit-box').children().remove();
    }
};

Mealz.prototype.selectDish = function ($element, e) {
    var $mealRow = $element.closest('.meal-row');
    var dishId = $element.data('attribute-id');

    // If it's a dish without variations
    if ($element.attr('data-attribute-parent') !== 'true') {
        // add remove icon
        $mealRow.find('.remove-meal').attr('style', 'display: block;');
        $mealRow.data('attribute-selected-dish', dishId);
        $mealRow.find('.meal-label').text($element.find('.dish-title').html());
        $mealRow.attr('data-attribute-selected-variations', '');
        $mealRow.find('.variation-checkbox').removeClass('checked');
        this.clearAllFormElements($mealRow);
        this.createMealFormElement($mealRow, dishId);
        this.hideSelectBox();
    }

    // Initialize the participation limit with 0
    if($mealRow.find('.participation-limit').val() === "" ){
        $mealRow.find('.participation-limit').val(0);
    }
};

Mealz.prototype.selectVariation = function ($element) {
    var parentId = $element.closest('.dishes').attr('data-attribute-id');
    var variationId = $element.parent('.variation').data('attribute-id');

    var $mealRow = $element.closest('.meal-row');
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
                if ($(this).find('input:first').val() == variationId) {
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

Mealz.prototype.hideSelectBox = function () {
    var $activeSelectBox = $('.meal-select-box:visible');
    this.hideVariationSelectBox($activeSelectBox);
    $activeSelectBox.hide();
};

Mealz.prototype.hideVariationSelectBox = function ($activeSelectBox) {
    if (typeof $activeSelectBox === 'undefined') {
        $activeSelectBox = $('.meal-select-box:visible');
    }

    var $activeDish = $activeSelectBox.find('.dishes.active');
    if ($activeDish.length !== 0) {
        $activeDish.children('.meal-select-variations:visible').hide();
        $activeDish.removeClass('active');
    }
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
    if($mealRow.find('.participation-limit').val() === "") {
        $mealRow.find('.participation-limit').val(0);
    }
};

Mealz.prototype.clearAllFormElements = function ($mealRow) {
    var that = this;
    $mealRow.find('.meal-selected').each(function () {
        that.deleteSingleSelection($(this));
    });
};

Mealz.prototype.deleteSingleSelection = function ($element) {
    if ($element.hasClass('meal-persisted') === true) {
        $element.find('input:first').val('');
    } else {
        $element.remove();
    }
};
