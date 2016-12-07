Mealz.prototype.toggleParticipation = function ($checkbox) {
    var that = this;
    var $participantsCount = $checkbox.closest('.wrapper-meal-actions').find('.participants-count');
    var url = $checkbox.attr('value');

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            if (data.redirect) {
                window.location.replace(data.redirect);
            }
            $checkbox.attr('value', data.url);
            $participantsCount.fadeOut('fast', function () {
                that.applyCheckboxClasses($checkbox);
                $participantsCount.find('span').text(data.participantsCount);
                $participantsCount.fadeIn('fast');
            });
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};

Mealz.prototype.toggleParticipationAdmin = function ($element) {
    var url = $element.attr('href');

    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (data) {
            $element.attr('href', data.url);
            $element.parent().toggleClass('participating');
            $element.text(data.actionText);
        },
        error: function (xhr) {
            console.log(xhr.status + ': ' + xhr.statusText);
        }
    });
};


Mealz.prototype.toggleGuestParticipation = function ($checkbox) {
    var that = this;
    var $participantsCount = $checkbox.closest('.meal-row').find('.participants-count');
    var actualCount = parseInt($participantsCount.find('span').html());
    $participantsCount.fadeOut('fast', function () {
        that.applyCheckboxClasses($checkbox);
        $participantsCount.find('span').text($checkbox.is(':checked') ? actualCount + 1 : actualCount - 1);
        $participantsCount.fadeIn('fast');
    });
};

Mealz.prototype.selectProfile = function () {
    var profiles = {
        data: $('.profile-list').data('attribute-profiles'),
        getValue: 'label',
        list: {
            match: {
                enabled: true
            }
        }
    };

    $('.profile').easyAutocomplete(profiles);
};

Mealz.prototype.toggleProfiles = function () {

};