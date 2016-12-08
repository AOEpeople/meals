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
    this.$editParticipationParticipants.on('click', function () {
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
            $icon.toggleClass('glyphicon-check');
            $icon.toggleClass('glyphicon-unchecked');
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

Mealz.prototype.initAutocomplete = function (profilesJson) {
    var profile = $('.profile');
    var profiles = {
        data: profilesJson,
        getValue: 'label',
        list: {
            match: {
                enabled: true
            },
            onSelectItemEvent: function () {
                var value = profile.getSelectedItemData().value;
                var selected = $('.easy-autocomplete-container li')[profile.getSelectedItemIndex()];
                $(selected).attr({'data-attribute-value': value});
            }
        }
    };
    profile.easyAutocomplete(profiles);
};

Mealz.prototype.addProfile = function () {
    var profileList = JSON.parse($('.profile-list').attr('data-attribute-profiles'));
    var prototype = $('.table-content').data('prototype');
    var selectedProfile = $('.easy-autocomplete-container li[class="selected"]');
    if(selectedProfile.length > 0) {
        var name = $(selectedProfile)[0].innerText;
        var userName = $(selectedProfile).data('attribute-value');
        buildPrototype(name, userName);
        addToTable(prototype, selectedProfile);
        buildNewProfileList(profileList, userName);
        Mealz.prototype.initToggleParticipation();
    }

    function buildPrototype(name, userName) {
        prototype = prototype.replace(/__name__/g, name);
        prototype = prototype.replace(/__username__/g, userName);
    }

    function addToTable(prototype, selectedProfile) {
        $(prototype).prependTo("table > tbody");
        selectedProfile.remove();
        $('.profile').val('');
    }

    function buildNewProfileList(profileList, userName) {
        var newProfileList = $.grep(profileList, function(e) { return e.value != userName; });
        $('.profile-list').attr('data-attribute-profiles', JSON.stringify(newProfileList));
    }
};

Mealz.prototype.showProfiles = function () {
    var input = $('.profile');
    Mealz.prototype.initAutocomplete(JSON.parse($('.profile-list').attr('data-attribute-profiles')));
    var profileListContainer = $('.easy-autocomplete-container ul');
    fillContainer(JSON.parse($('.profile-list').attr('data-attribute-profiles')));

    $(document).on("click", function () {
        profileListContainer.hide();
    });

    // if click on hamburger-menu show profile list
    $('.toggle-profiles').on('click', function (event) {
        input.val('');
        fillContainer(JSON.parse($('.profile-list').attr('data-attribute-profiles')));
        profileListContainer.toggle();
        event.preventDefault();
        event.stopPropagation();
    });

    // show all profiles in list
    function fillContainer(profileList) {
        profileListContainer.empty();
        $.each(profileList, function( key ) {
            profileListContainer.append('<li data-attribute-value=\"' + profileList[key].value + '\"><div class=\"eac-item\">' + profileList[key].label + '</div></li>');
        });
        selectProfile();
    }

    function selectProfile() {
        // if click li add class selected
        $('.easy-autocomplete-container li').on('click', function () {
            $(this).toggleClass('selected');
        });

        // if click on profil, add profil into input value
        $('.easy-autocomplete-container li div').on('click', function () {
            var selectedItem = $(this)[0].innerHTML;
            $('.profile').val(selectedItem);
        });
    }
};