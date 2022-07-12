import {ParticipationResponse} from './participation-response-handler';
import AjaxErrorHandler from './ajax-error-handler';

export class ParticipationRequest {
    readonly url: string;
    readonly data: {};
    readonly method: string;

    constructor(url: string, data?: {}) {
        this.url = url;
        this.method = 'GET';
        if (data) {
            this.data = data
            this.method = 'POST'
        }
    }
}

type ParticipationResponseHandlerMethod = ($checkbox: JQuery, response: ParticipationResponse) => void;

export class ParticipationRequestHandler {
    public static sendRequest(participationRequest: ParticipationRequest, $checkbox: JQuery, handle: ParticipationResponseHandlerMethod) {
        if (undefined === $checkbox) {
            console.log('Error: No checkbox found');
            return;
        }

        if (undefined === participationRequest.url || '' === participationRequest.url) {
            console.log('Error: URL is missing');
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
            error: function (jqXHR) {
                AjaxErrorHandler.handleError(jqXHR, function (){
                    if (true === ParticipationRequestHandler.isJoinRequest(participationRequest.url)) {
                        let mealTitle = $checkbox.closest('.meal-row').children('.title').text();
                        let errMsg = $('.weeks').data('errJoinNotPossible').replace('%dish%', mealTitle);
                        ParticipationRequestHandler.sendAlert(errMsg);
                    }
                });
            }
        });
    }

    private static isJoinRequest(url: string): boolean {
        return (url.indexOf('join') !== -1);
    }

    private static sendAlert(message: string):void {
        if (message.length > 0) {
            alert(message);
        }
    }
}
