const {CombinedMealDialog} = require("./combined-meal-dialog");
const {ParticipantCounter} = require("./participant-counter");

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
            slug: $mealRow.data('slug'),
            variations: []
        };
        $mealRow.find('.variation-row').each(function () {
            const $dishVarRow = $(this);
            let dishVariation = {
                title: $dishVarRow.find('.text-variation').text().trim(),
                slug: $dishVarRow.data('slug')
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
    const title = $dishCheckbox.closest('.meal-row').find('.title').text();
    const dishes = this.getCombinedMealDishes($dishCheckbox);
    const isGuestParticipation = $('body').hasClass('guest-wrapper');
    let cmd = new CombinedMealDialog(
        title,
        dishes,
        slotBox.val(),
        path,
        {
            ajax: !isGuestParticipation,
            ok: function (data) {
                $dishCheckbox.prop('checked', !$dishCheckbox.is(':checked'));
                self.participationToggleHandler.toggleCheckboxWrapperClasses($dishCheckbox);
                if (isGuestParticipation) {
                    self.participationToggleHandler.updateDishSelection($dishCheckbox, data);
                    self.participationToggleHandler.toggleGuest($dishCheckbox);
                } else {
                    let participantCounter = $dishCheckbox.data(ParticipantCounter.NAME);
                    if (participantCounter.getCount() !== data.participantsCount) {
                        participantCounter.setNextCount(data.participantsCount);
                        participantCounter.updateUI();
                    }
                    updateCheckbox($dishCheckbox, data.url);
                }
            }
        }
    );
    cmd.open();
}

function updateCheckbox($checkbox, url, checkboxClass) {
    $checkbox.attr('value', url);
    if (undefined !== checkboxClass) {
        $checkbox.attr('class', checkboxClass);
    }
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
