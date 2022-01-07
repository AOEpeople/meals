import {ActionResponse} from "./participation-response-handler";

export class ParticipationRequest {
    url: string;
    method: string;
    data: object;

    constructor($checkbox: JQuery) {
        this.url = $checkbox.attr('value');
        this.method = 'GET';
    }
}

export class JoinParticipationRequest extends ParticipationRequest {
    constructor($checkbox: JQuery) {
        super($checkbox);
        let $slotBox = $checkbox.closest('.meal').find('.slot-selector');
        this.method = 'POST';
        this.data = {
            'slot': $slotBox.val()
        };
    }
}

type ParticipationResponseHandlerMethod = ($checkbox: JQuery, response: ActionResponse) => void;

export class ParticipationRequestHandler {
    static sendRequest(participationRequest: ParticipationRequest, $checkbox: JQuery, handle: ParticipationResponseHandlerMethod) {
        if (undefined === $checkbox) {
            console.log('Error: No checkbox found');
            return;
        }

        $.ajax({
            method: participationRequest.method,
            url: participationRequest.url,
            data: participationRequest.data,
            dataType: 'json',
            success: function (data) {
                handle($checkbox, data);
            },
            error: function (xhr) {
                console.log(xhr.status + ': ' + xhr.statusText);
            }
        });
    }
}