import {ParticipationAction, ParticipationUpdateHandler} from "./participation-update-handler";

export class UpdateOffersHandler {
    private readonly updateInterval: number = 5000;

    constructor() {
        this.initCallback();
    }

    private initCallback(): void {
        if ($('.button-login').text() !== 'LOGIN') {
            window.setInterval(this.updateOffers, this.updateInterval);
        }
    }

    private updateOffers() {
        $.ajax({
            method: 'GET',
            url: '/menu/meal/update-offers',
            dataType: 'json',
            success: function (data) {
                $.each(data, function (mealId: number, offerStatus: any) {
                    let available = offerStatus[0];
                    let $mealWrapper = $('[data-id=' + mealId + ']');
                    let $checkbox = $mealWrapper.find('.participation-checkbox');

                    // new offer available and checkbox not checked yet
                    if (available === true &&
                        $checkbox.is(':checked') === false &&
                        $checkbox.hasClass(ParticipationAction.UNSWAP) === false &&
                        $checkbox.hasClass(ParticipationAction.ACCEPT_OFFER) === false &&
                        $checkbox.hasClass(ParticipationAction.JOIN) === false) {
                        let date = offerStatus[1];
                        let dishSlug = offerStatus[2];
                        ParticipationUpdateHandler.changeToOfferIsAvailable(
                            $checkbox,
                            '/menu/' + date + '/' + dishSlug + '/accept-offer'
                        );
                    }

                    // if a user's offer is gone and the participation-badge is still showing 'pending', disable the checkbox, tooltip and change badge
                    if ($checkbox.hasClass(ParticipationAction.UNSWAP) === true) {
                        let participantId = parseInt($checkbox.data('participant-id'));
                        if (isNaN(participantId)) {
                            console.log('Error: Participant ID is not a number');
                            return;
                        }
                        $.getJSON('/menu/meal/' + participantId + '/isParticipationPending', function (isParticipationPendingResponse) {
                            if (isParticipationPendingResponse[0] === false) {
                                ParticipationUpdateHandler.changeToOfferIsTaken($checkbox);
                            }
                        });
                    }

                    // no offer available (anymore)
                    if (available === false && $checkbox.hasClass(ParticipationAction.ACCEPT_OFFER) === true) {
                        ParticipationUpdateHandler.changeToOfferIsGone($checkbox);
                    }
                });
            },
            error: function () {
                window.location.replace('/');
            }
        });
    }
}