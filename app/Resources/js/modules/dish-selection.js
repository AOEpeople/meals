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

    // Hiding select-box if click anywhere else
    $('.meal-form, .meal-select-box').mouseup(function (e) {
        e.preventDefault();
        e.stopPropagation();
        that.hideVariationSelectBox(e);
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
    });

    $('.meal-select').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).prev().toggle();
    });
};

Mealz.prototype.selectDish = function ($element, e) {
    var $mealRow = $element.closest('.meal-row');
    var dishId = $element.data('attribute-id'); //$(e.currentTarget)

    if($element.attr('data-attribute-parent') != 'true') {
        $mealRow.data('attribute-selected-dish', dishId);
        $mealRow.find('.meal-label').text($element.find('.dish-title').html());
        $mealRow.attr('data-attribute-selected-variations', '');
        $mealRow.find('.variation-checkbox').removeClass("checked");
        $mealRow.find('.meal-selected').slice(1).remove();
        $mealRow.find('.meal-selected:first').find('input:first').val(dishId);
        this.hideVariationSelectBox(e);
    }
};

Mealz.prototype.selectVariation = function ($element) {
    var parentId = $element.closest('.dishes').attr('data-attribute-id');
    var variationId = $element.parent('.variation').data('attribute-id');

    var $mealRow = $element.closest('.meal-row');
    var selectedDish = $mealRow.data('attribute-selected-dish');
    var previousSelectedVariations = $mealRow.attr('data-attribute-selected-variations');
    var $input = $mealRow.children('.meal-selected').first();
    var variations = [];

    // If data-attribute selected-variations is defined and not empty
    if (previousSelectedVariations) {
        previousSelectedVariations = JSON.parse(previousSelectedVariations);
        variations = previousSelectedVariations;
    }

    toggleArrayItem(variations, variationId);

    // Set meal row data attributes and dropdown label
    $mealRow.attr('data-attribute-selected-dish', parentId);
    $mealRow.attr('data-attribute-selected-variations', JSON.stringify(variations));
    this.setDropdownLabelForSelectedVariations($mealRow, parentId, variations);

    // If checkbox wasn't checked before
    if(!$element.hasClass('checked')) {
        if (variations.length === 0) {
            $input.find('input').first().val(selectedDish);
        } else if (variations.length === 1) {
            $input.find('input').first().val(variations[0]);
        } else {
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
            $prototypeFormElementInputs.first().val(variationId);
        }
    } else {
        // If there are still variations selected
        if (variations.length > 0) {
            if (variations.length === 1) {
                $input.find('input').first().val(variations[0]);
                $mealRow.find('.meal-selected').slice(1).remove();
            } else {
                // Find and remove input field with the same value like the id of clicked variation
                $mealRow.find('.meal-selected').each(function () {
                    if($element.find('input:first').val() === variationId){
                        $element.remove();
                    }
                });
            }
            // Else (If no variation, and therefore no dish, is selected anymore)
        } else {
            // Remove every other input except first
            $mealRow.attr('data-attribute-selected-dish', '');
            $input.find('input:first').attr('value', '');
            $mealRow.find('.meal-selected').slice(1).remove();
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
    $mealRow.find('.meal-selected').slice(1).remove();
    $mealRow.find('.meal-selected').first().find('input').first().attr('value', '');

    //Remove tick from variation checkboxes
    $mealRow.find('.variation-checkbox.checked').removeClass('checked');
};

Mealz.prototype.setDropdownLabelForSelectedVariations = function (mealRow, parentId, variations) {
    var mealRowLabel = mealRow.find('.meal-label');
    if(variations.length > 0) {
        var $parentDish = $("li.dishes[data-attribute-id=" + parentId + "]");
        var parentLabel = $parentDish.find('.dish-title').html();
        var variationsLabel = '';

        for(var i = 0; i < variations.length; i++) {
            variationsLabel += (i > 0) ? ', ' : '';
            variationsLabel += $parentDish.find(".variation[data-attribute-id=" + variations[i] + "] label").html();
        }
        mealRowLabel.text(parentLabel + ' - ' + variationsLabel);
    } else {
        mealRowLabel.empty();
    }
};

Mealz.prototype.hideVariationSelectBox = function (e) {
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
};