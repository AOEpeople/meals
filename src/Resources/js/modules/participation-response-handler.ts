import {AcceptOfferData, ParticipationUpdateHandler, ToggleData} from './participation-update-handler';

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
    slot: string;
    available: boolean;
}


export class ParticipationResponseHandler {
    public static onToggle($checkbox: JQuery, response: ToggleResponse) {
        const data: ToggleData = {
            participantID: response.id,
            actionText: response.actionText,
            url: response.url,
            participantsCount: response.participantsCount,
            bookedDishSlugs: response.bookedDishSlugs,
            slot: response.slot,
            available: response.available
        };
        ParticipationUpdateHandler.toggle($checkbox, data);
    }

    public static onAcceptOffer($checkbox: JQuery, response: ToggleResponse) {
        const data: AcceptOfferData = {
            participantID: response.id,
            url: response.url,
            participantsCount: response.participantsCount,
            bookedDishSlugs: response.bookedDishSlugs,
        };
        ParticipationUpdateHandler.acceptOffer($checkbox, data);
    }

    public static onOffer($checkbox: JQuery, response: SwapResponse) {
        ParticipationUpdateHandler.setOffered($checkbox, response.url, response.id);
    }

    public static onRollbackOffer($checkbox: JQuery, response: SwapResponse) {
        ParticipationUpdateHandler.rollbackOffer($checkbox, response.url);
    }
}
