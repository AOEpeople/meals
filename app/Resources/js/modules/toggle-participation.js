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
    $.ajax({
        method: 'GET',
        url: url,
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

function swap($checkbox, url) {
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