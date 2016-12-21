
/**
 * Initialize and configurate plugin easyautocomplete
 */
Mealz.prototype.initAutocomplete = function () {
    var profile = $('.profile');
    var profiles = {
        data: JSON.parse($('.profile-list').attr('data-attribute-profiles')),
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

/**
 * Add new profil into participation list
 */
Mealz.prototype.addProfile = function () {
    var profileList = JSON.parse($('.profile-list').attr('data-attribute-profiles'));
    var prototype = $('.table-content').data('prototype');
    var selectedProfile = $('.easy-autocomplete-container li[class="selected"]');

    // if a profil is selected add profil to table
    if(selectedProfile.length > 0) {
        var name = $(selectedProfile)[0].innerText;
        var userName = $(selectedProfile).data('attribute-value');
        buildPrototype(name, userName);
        addToTable(prototype, selectedProfile);
        buildNewProfileList(profileList, userName);
    }

    // add name and username in prototype variable
    function buildPrototype(name, userName) {
        prototype = prototype.replace(/__name__/g, name);
        prototype = prototype.replace(/__username__/g, userName);
    }

    // remove input value and add new profil into the profil table
    function addToTable(prototype, selectedProfile) {
        $(prototype).prependTo('table > tbody');
        selectedProfile.remove();
        $('.profile').val('');
        $('.table').removeAttr('style');
        $('.empty-table').remove();
    }

    // build and show new profil list and intialize autocomplete
    function buildNewProfileList(profileList, userName) {
        var newProfileList = $.grep(profileList, function(e) { return e.value != userName; });
        $('.profile-list').attr('data-attribute-profiles', JSON.stringify(newProfileList));
        reinitialize();
    }

    // after adding a profile it is necessary to reinitialize to get new context
    function reinitialize() {
        Mealz.prototype.initAutocomplete();
        Mealz.prototype.showProfiles();
        Mealz.prototype.initToggleParticipation();
    }
};

/**
 * Show participation list
 */
Mealz.prototype.showProfiles = function () {
    var input = $('.profile');
    var profileListContainer = $('.easy-autocomplete-container ul');
    fillContainer(JSON.parse($('.profile-list').attr('data-attribute-profiles')));

    // close profil list select field if click next to list
    $(document).on('click', function () {
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
            profileListContainer.append('<li data-attribute-value="' + profileList[key].value + '"><div class="eac-item">' + profileList[key].label + '</div></li>');
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