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
    var url = $element.attr('data-action');

    $.ajax({
        method: 'POST',
        url: url,
        data: {
            'slot': ''
        },
        dataType: 'json',
        success: function (data) {
            $element.attr('data-action', data.url);
            $element.toggleClass('participating');
            let $icon = $element.find('i:first');
            $icon.toggleClass('glyphicon-check glyphicon-unchecked');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};
