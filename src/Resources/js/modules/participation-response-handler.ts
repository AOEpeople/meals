import {ParticipantCounter, ParticipationState} from "./participant-counter";
import {ParticipationUpdateHandler} from "./participation-update-handler";

export enum ParticipationAction {
    ACCEPT_OFFER = 'acceptOffer-action',
    DELETE_ACTION = 'delete-action',
    JOIN_ACTION = 'join-action',
    SWAP = 'swap-action',
    UNSWAP = 'unswap-action',
}

export interface ActionResponse {
    actionText: string;
    url: string;
}

interface ToggleResponse extends ActionResponse {
    participantsCount: number;
}

interface SwapResponse extends ActionResponse {
    id: number; // ID of participant
}

export class ParticipationResponseHandler {
    public static onSuccessfulToggle($checkbox: JQuery, response: ToggleResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationUpdateHandler.updateParticipationCounter(participantCounter, ParticipationState.DEFAULT, response.participantsCount)
        let action = $checkbox.is(':checked') ? ParticipationAction.DELETE_ACTION : ParticipationAction.JOIN_ACTION;
        ParticipationUpdateHandler.updateCheckbox($checkbox, action, response.url);
        ParticipationUpdateHandler.toggleCheckboxWrapperClasses($checkbox);

        let $slotBox = $checkbox.closest('.meal').find('.slot-selector');
        $slotBox.addClass('tmp-disabled').prop('disabled', true)
            .parent().children('.loader').css('visibility', 'visible');
    }

    public static onSuccessfulAcceptOffer($checkbox: JQuery, response: ToggleResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationUpdateHandler.updateParticipationCounter(participantCounter, ParticipationState.DEFAULT, response.participantsCount);
        ParticipationUpdateHandler.updateCheckbox($checkbox, ParticipationAction.SWAP, response.url)
        ParticipationUpdateHandler.toggleCheckboxWrapperClasses($checkbox);
        ParticipationUpdateHandler.toggleTooltip($checkbox);
    }

    public static onSuccessfulSwap($checkbox: JQuery, response: SwapResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationUpdateHandler.updateParticipationCounter(participantCounter, ParticipationState.PENDING);
        ParticipationUpdateHandler.updateCheckbox($checkbox, ParticipationAction.UNSWAP, response.url, response.id);
        ParticipationUpdateHandler.toggleTooltip($checkbox, 'offered');
    }

    public static onSuccessfulUnswap($checkbox: JQuery, response: SwapResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationUpdateHandler.updateParticipationCounter(participantCounter, ParticipationState.DEFAULT)
        ParticipationUpdateHandler.updateCheckbox($checkbox, ParticipationAction.SWAP, response.url);
        ParticipationUpdateHandler.toggleTooltip($checkbox);
    }
}