Mealz.prototype.toggleParticipation = function ($checkbox) {
    if ($checkbox === undefined) {
        console.log('Error: No checkbox found');
        return;
    }

    if (this.combiMealCheckbox($checkbox)) {
        this.showMealSelectionOverlay($checkbox);
    }

    var that = this;
    var url = $checkbox.attr('value');
    $participantsCount = $checkbox.closest('.wrapper-meal-actions').find('.participants-count');
    $tooltip = $checkbox.closest('.wrapper-meal-actions').find('.tooltiptext');

    var swapCheckbox = 'participation-checkbox swap-action';
    var unswapCheckbox = 'participation-checkbox unswap-action';
    var acceptOfferCheckbox = 'participation-checkbox acceptOffer-action';

    if ($checkbox.hasClass(swapCheckbox)) {
        confirmSwap($checkbox);
    } else if ($checkbox.hasClass(unswapCheckbox)) {
        $checkbox.attr('class', 'progressing');
        unswap($checkbox, url, swapCheckbox);
    } else if ($checkbox.hasClass(acceptOfferCheckbox)) {
        acceptOffer($checkbox, url, that, swapCheckbox);
    } else {
        toggle($checkbox, url, that);
    }
};

Mealz.prototype.combiMealCheckbox = function ($dishCheckbox) {
    return '1' === $dishCheckbox.closest('.meal-row').attr('data-combi');
}

Mealz.prototype.mealHasVariations = function ($dishCheckbox) {
    return 0 < $dishCheckbox.closest('.meal-row').find('.variation-row .text-variation').length;
}

Mealz.prototype.getCombiMealDishes = function ($dishCheckbox) {
    let dishes = [];
    $dishCheckbox.closest('.meal').find('.meal-row').each(function () {
        const $mealRow = $(this);
        if ('1' === $mealRow.attr('data-combi')) {
            return;
        }

        let dish = {
            title: $mealRow.find('.text .title').text().trim(),
            variations: []
        };
        $mealRow.find('.variation-row .text-variation').each(function () {
            let dishVariation = { title: $(this).text().trim() };
            dish.variations.push(dishVariation);
        });
        dishes.push(dish);
    });

    return dishes;
}

Mealz.prototype.showMealSelectionOverlay = function ($dishCheckbox) {
    const dishes = this.getCombiMealDishes($dishCheckbox);
    const combiMealTitle = dishes[0].title + ' & ' + dishes[1].title;
    let $mealWrapper = $('<div class="meal-wrapper"></div>');
    $mealWrapper.append('<div class="title">' + combiMealTitle + '</div>');

    dishes.forEach((dish) => {
        let $dishWrapper = $('<div class="dish-wrapper"></div>');
        let $dish = $('<div class="dish"></div>');

        if (0 === dish.variations.length) {
            $dish.append('<label for="">' + dish.title + '</label><input type="radio">');
            $dishWrapper.append($dish);
            $mealWrapper.append($dishWrapper);
            return;
        }

        $dish.text(dish.title);

        dish.variations.forEach((dishVariation) => {
            let $dishVariation = $('<div class="variation"></div>');
            $dishVariation.append('<label for="">' + dishVariation.title + '</label><input type="radio">');
            $dishWrapper.append($dishVariation);
        });

        $mealWrapper.append($dishWrapper);
    });

    let $container = $('#combi-meal-selector');
    $container.empty().append($mealWrapper);
    let options = {};
    $.fancybox.open($container, options);
}

function editCountAndCheckbox(data, $checkbox, countClass, checkboxClass) {
    $checkbox.attr('value', data.url);
    $participantsCount.fadeOut('fast', function () {
        $participantsCount.find('span').text(data.participantsCount);

        if (checkboxClass !== undefined) {
            $checkbox.attr('class', checkboxClass);
        }

        if (countClass !== undefined) {
            $participantsCount.toggleClass(countClass);
        }

        $participantsCount.fadeIn('fast');
    });
}

function toggle($checkbox, url, that) {
    const slot = $checkbox.closest('.meal').find('.slot-selector').val();
    $.ajax({
        method: 'POST',
        url: url,
        data: {
            'slot': slot
        },
        dataType: 'json',
        success: function (data) {
            editCountAndCheckbox(data, $checkbox);
            that.applyCheckboxClasses($checkbox);
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

function confirmSwap($checkbox) {
    // make checkbox public for reference in twig template
    swapCheckbox = $checkbox;
    Mealz.prototype.enableConfirmSwapbox($checkbox.attr('value'));
}

window.swap = function ($checkbox, url) {
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            var countClass = 'participation-pending';
            var checkboxClass = 'participation-checkbox unswap-action';
            editCountAndCheckbox(data, $checkbox, countClass, checkboxClass);

            $tooltip.toggleClass('active');

            //get text for tooltip
            $.getJSON('/labels.json')
                .done(function (data) {
                    if ($('.language-switch').find('span').text() === 'de') {
                        $tooltip.text(data[1].tooltip_DE[0].offered);
                    } else {
                        $tooltip.text(data[0].tooltip_EN[0].offered);
                    }
                });

            $checkbox.attr('participantid', data.id);

        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

function unswap($checkbox, url, swapCheckbox) {
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            var countClass = 'participation-pending';
            editCountAndCheckbox(data, $checkbox, countClass, swapCheckbox);
            $tooltip.toggleClass('active');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

function acceptOffer($checkbox, url, that, swapCheckbox) {
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            that.applyCheckboxClasses($checkbox);
            var countClass = 'offer-available';
            editCountAndCheckbox(data, $checkbox, countClass, swapCheckbox);
            $tooltip.toggleClass('active');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

Mealz.prototype.loadToggleParticipationCheckbox = function ($tableRow) {
    var that = this;
    var $tds = $tableRow.find('.table-data.meal-participation');
    var hasEditing = $tableRow.hasClass('editing');
    var $editableRows = $('.container.edit-participation .table-row.editing');

    if (this.$editParticipationEventListener !== undefined) {
        this.$editParticipationEventListener.off();
    }

    $editableRows.removeClass('editing');
    $editableRows.find('.table-data').each(function (idx, td) {
        var $td = $(td);
        var $icon = $td.find('i:first');
        var iconClass = 'glyphicon';
        if ($td.hasClass('participating')) {
            iconClass += ' glyphicon-ok';
        }
        $icon.attr('class', iconClass);
    });

    if (!hasEditing) {
        $tableRow.addClass('editing');
        $tds.each(function (idx, td) {
            var $td = $(td);
            var iconClass = $td.hasClass('participating') ? 'glyphicon-check' : 'glyphicon-unchecked';

            $td.find('i:first').addClass(iconClass);
        });
        $tds.on('click', function () {
            that.toggleParticipationAdmin($(this));
        });
        this.$editParticipationEventListener = $tds;
    }
};

Mealz.prototype.initToggleParticipation = function () {
    var that = this;
    this.$editParticipationParticipants = $('.container.edit-participation td.text');
    this.$editParticipationParticipants.off().on('click', function () {
        that.loadToggleParticipationCheckbox($(this).parent('.table-row'));
    });
};

Mealz.prototype.toggleParticipationAdmin = function ($element) {
    var url = $element.data('attribute-action');

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $element.data('attribute-action', data.url);
            $element.toggleClass('participating');
            var $icon = $element.find('i:first');
            $icon.toggleClass('glyphicon-check glyphicon-unchecked');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

Mealz.prototype.toggleGuestParticipation = function ($checkbox) {
    var that = this;
    var $participantsCount = $checkbox.parents('.action').parent().find('.participants-count');
    var actualCount = parseInt($participantsCount.find('span').html());
    $participantsCount.fadeOut('fast', function () {
        that.applyCheckboxClasses($checkbox);
        $participantsCount.find('span').text($checkbox.is(':checked') ? actualCount + 1 : actualCount - 1);
        $participantsCount.fadeIn('fast');
    });
};
