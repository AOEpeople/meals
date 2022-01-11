import {ActionResponse} from "./participation-response-handler";

export class ParticipationRequest {
    readonly url: string;
    readonly data: {};
    readonly method: string;

    constructor($checkbox: JQuery, data?: {}) {
        this.url = $checkbox.attr('value');
        this.method = 'GET';
        if (data) {
            this.data = data
            this.method = 'POST'
        }
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