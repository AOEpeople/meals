import {ParticipationUpdateHandler} from "./participation-update-handler";

export enum MealOfferStates {
    New = 'new',
    Accepted = 'accepted',
    Gone = 'gone'
}

export interface MealOfferUpdate {
    state: MealOfferStates;
    mealId: number;
    participantId?: number; // Updated participant's ID
    available?: boolean;    // Flag to specify if the meal is still available or not
}

export class MealOfferUpdateHandler {

    static handleUpdate($checkbox: JQuery, data: MealOfferUpdate): void {
        switch (data.state) {
            case MealOfferStates.New:
                let dishSlug = $checkbox.closest('[data-slug]').data('slug');
                let date = $checkbox.closest('.meal').data('date');
                MealOfferUpdateHandler.handleNewMealOffer($checkbox, date, dishSlug);

                break;
            case MealOfferStates.Accepted:
                if (data.participantId === undefined) {
                    console.log('error: invalid meal-offer update; property "offerer-id" not found');
                    return;
                }
                if (data.available === undefined) {
                    console.log('error: invalid meal-offer update; property "available" not found');
                    return;
                }

                MealOfferUpdateHandler.handleMealOfferAccepted($checkbox, data.participantId, data.available);

                break;
            case MealOfferStates.Gone:
                ParticipationUpdateHandler.changeToOfferIsGone($checkbox);

                break;
        }
    }

    /**
     * Handles event when a meal is offered.
     */
    static handleNewMealOffer($checkbox: JQuery, date: string, dishSlug: string): void {
        if ($checkbox.is(':checked')) {
            return; // user already has the meal booked
        }

        const nextAction = $checkbox.attr('data-action');
        if (undefined !== nextAction && '' !== nextAction) {
            return; // Meal is open to overtake, i.e. accept offered meal
        }

        ParticipationUpdateHandler.changeToOfferIsAvailable(
            $checkbox,
            `/menu/${date}/${dishSlug}/accept-offer`
        );
    }

    /**
     * Handles event when an offered meals is accepted by some user.
     *
     * @param $checkbox Meal Checkbox
     * @param offererId Participant-ID of the accepted meal
     * @param available Is the accepted/taken meal is still available, i.e. still being offered
     */
    static handleMealOfferAccepted($checkbox: JQuery, offererId: number, available: boolean): void {
        if (isNaN(offererId)) {
            console.log('Error: Participant-ID is not a number');
            return;
        }
        // change participation state for
        // - offerer whose meal has been taken, or
        // - everyone if meal is no longer available
        if (offererId === $checkbox.data('participantId') || !available) {
            ParticipationUpdateHandler.offerAccepted($checkbox, offererId, available);
        }
    }
}
