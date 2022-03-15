import {ParticipationAction, ParticipationUpdateHandler} from "./participation-update-handler";

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

                MealOfferUpdateHandler.handleMealOfferAccepted($checkbox, data.participantId);

                if (!data.available) {
                    MealOfferUpdateHandler.handleAllTaken($checkbox);
                }

                break;
            case MealOfferStates.Gone:
                MealOfferUpdateHandler.handleAllTaken($checkbox);
                break;
        }
    }

    /**
     * Handles event when a meal is offered.
     */
    static handleNewMealOffer($checkbox: JQuery, date: string, dishSlug: string): void {
        if ($checkbox.is(':checked') === false &&
            $checkbox.hasClass(ParticipationAction.UNSWAP) === false &&
            $checkbox.hasClass(ParticipationAction.ACCEPT_OFFER) === false &&
            $checkbox.hasClass(ParticipationAction.JOIN) === false) {
            ParticipationUpdateHandler.changeToOfferIsAvailable(
                $checkbox,
                `/menu/${date}/${dishSlug}/accept-offer`
            );
        }
    }

    /**
     * Handles event when an offered meals is accepted by some user.
     */
    static handleMealOfferAccepted($checkbox: JQuery, participantId: number): void {
        if ($checkbox.hasClass(ParticipationAction.UNSWAP) === true) {
            if (isNaN(participantId)) {
                console.log('Error: Participant-ID is not a number');
                return;
            }
            if (participantId === $checkbox.data('participantId')) {
                ParticipationUpdateHandler.changeToOfferIsTaken($checkbox);
            }
        }
    }

    /**
     * Handles event when all offered meals are taken.
     */
    static handleAllTaken($checkbox: JQuery): void {
        if ($checkbox.hasClass(ParticipationAction.ACCEPT_OFFER) === true) {
            ParticipationUpdateHandler.changeToOfferIsGone($checkbox);
        }
    }
}
