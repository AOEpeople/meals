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
            that.toggleParticipationAdminNew($(this));
        });
        this.$editParticipationEventListener = $tds;
    }
};

Mealz.prototype.initToggleParticipation = function () {
    var that = this;
    this.$editParticipationParticipants = $('.container.edit-participation td.text');
    this.$editParticipationParticipants.on('click', function () {
        console.log("TOGGLE");
        that.loadToggleParticipationCheckbox($(this).parent('.table-row'));
    });
};

Mealz.prototype.toggleParticipationAdminNew = function ($element) {
    console.log($element);
    var url = $element.data('attribute-action');

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $element.data('attribute-action', data.url);
            $element.toggleClass('participating');
            var $icon = $element.find('i:first');
            $icon.toggleClass('glyphicon-check');
            $icon.toggleClass('glyphicon-unchecked');
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};