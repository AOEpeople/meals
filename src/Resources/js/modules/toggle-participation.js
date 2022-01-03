const {CombinedMealDialog} = require("./combined-meal-dialog");
const {ParticipationState} = require("./participant-counter");

Mealz.prototype.toggleParticipation = function ($checkbox) {
    if ($checkbox === undefined) {
        console.log('Error: No checkbox found');
        return;
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

Mealz.prototype.mealHasVariations = function ($dishCheckbox) {
    return 0 < $dishCheckbox.closest('.meal').find('.variation-row .text-variation').length;
}

Mealz.prototype.getCombinedMealDishes = function ($dishCheckbox) {
    let dishes = [];
    $dishCheckbox.closest('.meal').find('.meal-row').each(function () {
        const $mealRow = $(this);
        if (1 === $mealRow.data('combined')) {
            return;
        }

        let dish = {
            title: $mealRow.find('.text .title').contents().get(0).nodeValue.trim(),
            slug: $mealRow.data('id'),
            variations: []
        };
        $mealRow.find('.variation-row').each(function () {
            const $dishVarRow = $(this);
            let dishVariation = {
                title: $dishVarRow.find('.text-variation').text().trim(),
                slug: $dishVarRow.data('id')
            };
            dish.variations.push(dishVariation);
        });
        dishes.push(dish);
    });

    return dishes;
}

Mealz.prototype.showMealSelectionOverlay = function ($dishCheckbox) {
    let self = this;
    let path = $dishCheckbox.attr('value');
    const slotBox = $dishCheckbox.closest('.meal').find('.slot-selector');
    const dishes = this.getCombinedMealDishes($dishCheckbox);
    const isGuestParticipation = $('body').hasClass('guest-wrapper');
    let cmd = new CombinedMealDialog(
        dishes,
        slotBox.val(),
        path,
        {
            ajax: !isGuestParticipation,
            ok: function (data) {
                $dishCheckbox.prop('checked', !$dishCheckbox.is(':checked'));
                self.applyCheckboxClasses($dishCheckbox);
                if (isGuestParticipation) {
                    updateDishSelection($dishCheckbox, data);
                    self.toggleGuestParticipation($dishCheckbox);
                } else {
                    let participantCounter = $dishCheckbox.data('participantCounter');
                    participantCounter.setCounter(data.participantsCount);
                    participantCounter.updateUI();
                    updateCheckbox($dishCheckbox, data.url);
                }
            }
        }
    );
    cmd.open();
}

function updateDishSelection($dishCheckbox, data) {
    let $mealRow = $dishCheckbox.parents('.meal-row');
    let $dishSelectionWrapper = $mealRow.find("#dish-selection-wrapper");
    if (0 === $dishSelectionWrapper.length) {
        $dishSelectionWrapper = $('<div id="dish-selection-wrapper"></div>');
        $mealRow.prepend($dishSelectionWrapper);
    } else {
        $dishSelectionWrapper.empty();
    }

    data.filter(entry => entry.name.startsWith('dishes')).forEach(entry => {
        let $dishSelectionField = '<input type="hidden" name="' + entry.name + '" value="' + entry.value + '">';
        $dishSelectionWrapper.prepend($dishSelectionField);
    });
}

function updateCheckbox($checkbox, url, checkboxClass) {
    $checkbox.attr('value', url);
    if (undefined !== checkboxClass) {
        $checkbox.attr('class', checkboxClass);
    }
}

function toggle($checkbox, url, that) {
    const slotBox = $checkbox.closest('.meal').find('.slot-selector');
    $.ajax({
        method: 'POST',
        url: url,
        data: {
            'slot': slotBox.val()
        },
        dataType: 'json',
        success: function (data) {
            let participantCounter = $checkbox.data('participantCounter');
            participantCounter.setCounter(data.participantsCount);
            participantCounter.updateUI();
            updateCheckbox($checkbox, data.url);
            that.applyCheckboxClasses($checkbox);
            slotBox.addClass('tmp-disabled').prop('disabled', true)
                .parent().children('.loader').css('visibility', 'visible');
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
            let unswapCheckbox = 'participation-checkbox unswap-action';

            let participantCounter = $checkbox.data('participantCounter');
            participantCounter.setCounter(data.participantsCount);
            participantCounter.setParticipationState(ParticipationState.PENDING);
            participantCounter.updateUI();
            updateCheckbox($checkbox, data.url, unswapCheckbox);

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
            let participantCounter = $checkbox.data('participantCounter');
            participantCounter.setCounter(data.participantsCount);
            participantCounter.setParticipationState(ParticipationState.PENDING);
            participantCounter.updateUI();
            updateCheckbox($checkbox, data.url, swapCheckbox);
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
            let participantCounter = $checkbox.data('participantCounter');
            participantCounter.setCounter(data.participantsCount);
            participantCounter.setParticipationState(ParticipationState.OFFER_AVAILABLE);
            participantCounter.updateUI();
            updateCheckbox($checkbox, data.url, swapCheckbox)
            $tooltip.toggleClass('active');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

Mealz.prototype.loadToggleParticipationCheckbox = function ($tableRow) {
    var that = this;
    let $tds = $tableRow.find('.table-data.meal-participation');
    let hasEditing = $tableRow.hasClass('editing');
    let $editableRows = $('.container.edit-participation .table-row.editing');

    if (this.$editParticipationEventListener !== undefined) {
        this.$editParticipationEventListener.off();
    }

    $editableRows.removeClass('editing');
    $editableRows.find('.table-data').each(function (idx, td) {
        let $td = $(td);
        let $icon = $td.find('i:first');
        let iconClass = 'glyphicon';
        if ($td.hasClass('participating')) {
            iconClass += ' glyphicon-ok';
        }
        $icon.attr('class', iconClass);
    });

    if (!hasEditing) {
        $tableRow.addClass('editing');
        $tds.each(function (idx, td) {
            let $td = $(td);
            let iconClass = $td.hasClass('participating') ? 'glyphicon-check' : 'glyphicon-unchecked';

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
        method: 'POST',
        url: url,
        data: {
            'slot': ''
        },
        dataType: 'json',
        success: function (data) {
            $element.data('attribute-action', data.url);
            $element.toggleClass('participating');
            let $icon = $element.find('i:first');
            $icon.toggleClass('glyphicon-check glyphicon-unchecked');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

Mealz.prototype.toggleGuestParticipation = function ($checkbox) {
    let participantCounter = $checkbox.data('participantCounter');
    participantCounter.setCounter($checkbox.is(':checked') ? participantCounter.getCounter() + 1 : participantCounter.getCounter() - 1);
    participantCounter.updateUI();

    if (1 === $checkbox.parents('.meal-row').data('combined') && !$checkbox.is(':checked')) {
        updateDishSelection($checkbox, []);
    }
};
