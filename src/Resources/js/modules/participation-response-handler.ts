import {AcceptOfferData, ParticipationUpdateHandler, ToggleData} from "./participation-update-handler";

export interface ParticipationResponse {
    actionText: string;
}

export interface ActionResponse extends ParticipationResponse {
    url: string;
}

interface SwapResponse extends ActionResponse {
    id: number; // Participant ID
}

export interface ToggleResponse extends SwapResponse {
    participantsCount: number;
    bookedDishSlugs: string[];
}


export class ParticipationResponseHandler {
    public static onSuccessfulToggle($checkbox: JQuery, response: ToggleResponse) {
        const data: ToggleData = {
            participantID: response.id,
            actionText: response.actionText,
            url: response.url,
            participantsCount: response.participantsCount,
            bookedDishSlugs: response.bookedDishSlugs,
        };
        ParticipationUpdateHandler.toggleAction($checkbox, data);
    }

    public static onSuccessfulAcceptOffer($checkbox: JQuery, response: ToggleResponse) {
        const data: AcceptOfferData = {
            participantID: response.id,
            url: response.url,
            participantsCount: response.participantsCount,
            bookedDishSlugs: response.bookedDishSlugs,
        };
        ParticipationUpdateHandler.acceptOfferAction($checkbox, data);
    }

    public static onSuccessfulSwap($checkbox: JQuery, response: SwapResponse) {
        ParticipationUpdateHandler.changeToUnswapState($checkbox, response.url, response.id);
    }

    public static onSuccessfulUnswap($checkbox: JQuery, response: SwapResponse) {
        ParticipationUpdateHandler.changeToSwapState($checkbox, response.url);
    }
}
