Mealz.prototype.initUpdateSwappableMeals = function () {
    myTimer = window.setInterval(updateSwappableMeals, 1000);
};


function updateSwappableMeals() {
    $.getJSON('/menu/meal/getSwappableMeals', function (data) {
        $.each(data, function (mealId, value) {
            var mealWrapper = $("[data-id=" + mealId + "]");
            userCheckbox = mealWrapper.find('.checkbox-wrapper');
            participationCheckbox = mealWrapper.find('.participation-checkbox');
            tooltiptext = mealWrapper.find('.tooltiptext');

            //new offer available and checkbox not checked yet
            if (value[0] === true && userCheckbox.attr('class') !== 'checkbox-wrapper checked'
                && !participationCheckbox.hasClass('participation-checkbox acceptOffer-action')
                && !participationCheckbox.hasClass('participation-checkbox join-action')) {
                //activate tooltip and adapt it's text to the chosen language
                tooltiptext.addClass('active');
                if ($('.language-switch').find('span').text() === 'de') {
                    tooltiptext.text('1 Essen im Angebot.');
                } else
                    tooltiptext.text('There is one offered dish available.');
                //enable checkbox wrapper
                userCheckbox.removeClass('disabled');
                //enable checkbox
                participationCheckbox.removeAttr('disabled');
                //adapt checkbox class
                participationCheckbox.attr('class', 'participation-checkbox acceptOffer-action');
                //change checkbox value
                participationCheckbox.val('/menu/' + value[1] + '/' + value[2] + '/acceptOffer');
                //make participants counter green
                mealWrapper.find("#participants-count").fadeOut('fast');
                mealWrapper.find("#participants-count").addClass('offer-available');
                mealWrapper.find("#participants-count").fadeIn('fast');
            }

            //if a user's offer is gone and the participation-badge is still showing "pending", disable the checkbox, tooltip and change badge
            if (participationCheckbox.attr('class') === 'participation-checkbox unswap-action') {
                participantId = participationCheckbox.attr('participantId');
                $.getJSON('/menu/meal/' + participantId + '/isParticipationPending', function (data) {
                    if (data === false) {
                        //disable checkbox
                        mealWrapper.find('.participation-checkbox.unswap-action').parent().attr('class', 'checkbox-wrapper disabled');
                        //change checkbox class
                        mealWrapper.find('.participation-checkbox.unswap-action').attr('class', 'participation-checkbox');
                        //make participants counter grey
                        mealWrapper.find("#participants-count").fadeOut('fast');
                        mealWrapper.find("#participants-count").attr('class', 'participants-count');
                        mealWrapper.find("#participants-count").fadeIn('fast');
                        //deactivate tooltip
                        tooltiptext.removeClass('active');
                    }
                });
            }

            //no offer available (anymore)
            if (value[0] === false && mealWrapper.find("#participants-count").hasClass('offer-available') ||
                value[0] === false && mealWrapper.find("#participants-count").hasClass('participation-allowed')) {

                if (participationCheckbox.hasClass('delete-action')) {
                    //don't do anything
                }

                //make participants counter grey
                mealWrapper.find("#participants-count").fadeOut('fast');
                mealWrapper.find("#participants-count").attr('class', 'participants-count');
                mealWrapper.find("#participants-count").fadeIn('fast');
                //deactivate tooltip
                tooltiptext.removeClass('active');
                //remove class from checkbox
                participationCheckbox.removeClass('acceptOffer-action');

                //disable checkboxes that are not checked
                if (userCheckbox.attr('class') !== 'checkbox-wrapper checked') {
                    //disable checkbox wrapper
                    userCheckbox.addClass('disabled');
                    //disable checkbox
                    participationCheckbox.attr('disabled', 'disabled');
                }
            }
        });
    });
}