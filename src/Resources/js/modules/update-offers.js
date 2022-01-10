import {ParticipantCounter, ParticipationState} from "./participant-counter";

Mealz.prototype.updateOffers = function () {
    if ($('.button-login').text() !== 'LOGIN') {
        window.setInterval(updateOffers, 5000);
    }
};

function updateOffers() {
    $.ajax({
        method: 'GET',
        url: '/menu/meal/update-offers',
        dataType: 'json',
        success: function (data) {

            $.each(data, function (mealId, value) {
                var $mealWrapper = $('[data-id=' + mealId + ']');
                var $checkboxWrapper = $mealWrapper.find('.checkbox-wrapper');
                var $checkbox = $mealWrapper.find('.participation-checkbox');
                var $tooltip = $mealWrapper.find('.tooltiptext');
                let participantCounter = $checkbox.data(ParticipantCounter.NAME);

                //new offer available and checkbox not checked yet
                if (value[0] === true && $checkboxWrapper.hasClass('checked') === false &&
                    $checkbox.hasClass('acceptOffer-action') === false &&
                    $checkbox.hasClass('join-action') === false) {
                    //activate tooltip
                    $tooltip.addClass('active');
                    //get text for tooltip
                    $.getJSON('/labels.json')
                        .done(function (data) {
                            if ($('.language-switch').find('span').text() === 'de') {
                                $tooltip.text(data[1].tooltip_DE[0].available);
                            } else {
                                $tooltip.text(data[0].tooltip_EN[0].available);
                            }
                        });
                    //enable checkbox wrapper
                    $checkboxWrapper.removeClass('disabled');
                    //enable checkbox
                    $checkbox.removeAttr('disabled')
                        //adapt checkbox class
                        .attr('class', 'participation-checkbox acceptOffer-action')
                        //change checkbox value
                        .val('/menu/' + value[1] + '/' + value[2] + '/accept-offer');
                    //make participants counter green
                    if (ParticipationState.OFFER_AVAILABLE !== participantCounter.getParticipationState()) {
                        participantCounter.setNextParticipationState(ParticipationState.OFFER_AVAILABLE);
                        participantCounter.updateUI();
                    }
                }

                //if a user's offer is gone and the participation-badge is still showing 'pending', disable the checkbox, tooltip and change badge
                if ($checkbox.hasClass('participation-checkbox unswap-action') === true) {
                    let participantId = parseInt($checkbox.data('participant-id'));
                    if (isNaN(participantId)) {
                        console.log('Error: No participant ID found');
                        return;
                    }
                    $.getJSON('/menu/meal/' + participantId + '/isParticipationPending', function (data) {
                        if (data === false) {

                            $mealWrapper.find('.participation-checkbox.unswap-action')
                                //change checkbox class
                                .removeClass('unswap-action')
                                //disable checkbox
                                .parent().attr('class', 'checkbox-wrapper disabled');
                            //make participants counter grey
                            if (ParticipationState.DEFAULT !== participantCounter.getParticipationState()) {
                                participantCounter.setNextParticipationState(ParticipationState.DEFAULT);
                                participantCounter.updateUI();
                            }
                            //deactivate tooltip
                            $tooltip.removeClass('active');
                        }
                    });
                }

                //no offer available (anymore)
                if (value[0] === null && ParticipationState.OFFER_AVAILABLE === participantCounter.getParticipationState() ||
                    value[0] === null && true === participantCounter.isAvailable()) {

                    //make participants counter grey
                    if (ParticipationState.DEFAULT !== participantCounter.getParticipationState()) {
                        participantCounter.setNextParticipationState(ParticipationState.DEFAULT);
                        participantCounter.updateUI();
                    }
                    //deactivate tooltip
                    $tooltip.removeClass('active');
                    //remove class from checkbox
                    $checkbox.removeClass('acceptOffer-action');

                    //disable checkboxes that are not checked
                    if ($checkboxWrapper.hasClass('checked') === false) {
                        //disable checkbox wrapper
                        $checkboxWrapper.addClass('disabled');
                        //disable checkbox
                        $checkbox.attr('disabled', 'disabled');
                    }
                }
            });
        },
        error: function () {
            window.location.replace('/');
        }
    });
}
