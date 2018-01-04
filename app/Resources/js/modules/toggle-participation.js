Mealz.prototype.toggleParticipation = function ($checkbox) {
    that = this;
    $participantsCount = $checkbox.closest('.wrapper-meal-actions').find('.participants-count');
    $tooltiptext = $checkbox.closest('.wrapper-meal-actions').find('.tooltiptext');
    url = $checkbox.attr('value');
    d = new Date();



    if ($checkbox.attr('class') === "participation-checkbox swap-action") {
        confirmSwap($checkbox);
    } else if ($checkbox.attr('class') === "participation-checkbox unswap-action") {
        $checkbox.attr('class', 'in progress');
        unswap($checkbox);
    } else if ($checkbox.attr('class') === "participation-checkbox acceptOffer-action") {
        acceptOffer($checkbox);
    } else {
        toggle($checkbox);
    }
};

function editCountAndCheckbox(data, $checkbox, $countClass, $checkboxClass) {
    if (data.redirect) {
        window.location.replace(data.redirect);
    }

    $checkbox.attr('value', data.url);
    $participantsCount.fadeOut('fast', function () {
        $participantsCount.find('#participantsCount').text(data.participantsCount);

        if ($checkboxClass !== undefined) {
            $checkbox.attr('class', $checkboxClass);
        }

        if ($countClass !== undefined) {
            $participantsCount.toggleClass($countClass);
        }


        $participantsCount.fadeIn('fast');
    });
}


function toggle($checkbox) {
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
    $checkboxValue = $checkbox.attr('value');
    $link = $checkbox;
    Mealz.prototype.enableConfirmSwapbox();
}

function swap($checkbox) {
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $countClass = 'participation-pending';
            $checkboxClass = 'participation-checkbox unswap-action';
            editCountAndCheckbox(data, $checkbox, $countClass, $checkboxClass);
            $tooltiptext.toggleClass('active');

            $checkbox.attr('participantid', data.id);

            if ($('.language-switch').find('span').text() === 'de') {
                $tooltiptext.text('Jemand anderes kann jetzt dein Essen übernehmen.');
            } else {
                $tooltiptext.text('Someone else can take your meal now.');
            }
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

function unswap($checkbox) {
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $countClass = 'participation-pending';
            $checkboxClass = 'participation-checkbox swap-action';
            editCountAndCheckbox(data, $checkbox, $countClass, $checkboxClass);
            $tooltiptext.toggleClass('active');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
}

function acceptOffer($checkbox) {
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            that.applyCheckboxClasses($checkbox);
            $countClass = 'offer-available';
            $checkboxClass = 'participation-checkbox swap-action';
            editCountAndCheckbox(data, $checkbox, $countClass, $checkboxClass);
            $tooltiptext.toggleClass('active');
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