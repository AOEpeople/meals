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
        let nextAction = ('deleted' === response.actionText) ? ParticipationAction.JOIN_ACTION : ParticipationAction.DELETE_ACTION;
        ParticipationUpdateHandler.changeToNextAction($checkbox, nextAction, response.url, response.participantsCount);
    }

    public static onSuccessfulAcceptOffer($checkbox: JQuery, response: ToggleResponse) {
        ParticipationUpdateHandler.changeToSwapState($checkbox, response.url, response.participantsCount);
    }

    public static onSuccessfulSwap($checkbox: JQuery, response: SwapResponse) {
        ParticipationUpdateHandler.changeToUnswapState($checkbox, response.url, response.id);
    }

    public static onSuccessfulUnswap($checkbox: JQuery, response: SwapResponse) {
        ParticipationUpdateHandler.changeToSwapState($checkbox, response.url);
    }
}