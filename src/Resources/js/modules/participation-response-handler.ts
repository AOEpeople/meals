import {ParticipationUpdateHandler} from "./participation-update-handler";

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
    bookedDishes: string[];
}


export class ParticipationResponseHandler {
    public static onSuccessfulToggle($checkbox: JQuery, response: ToggleResponse) {
        ParticipationUpdateHandler.toggleAction($checkbox, response);
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
