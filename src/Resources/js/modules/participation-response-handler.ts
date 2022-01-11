import {ParticipantCounter, ParticipationState} from "./participant-counter";

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
        ParticipationResponseHandler.updateParticipantCounter(participantCounter, ParticipationState.DEFAULT, response.participantsCount)
        let action = $checkbox.is(':checked') ? ParticipationAction.DELETE_ACTION : ParticipationAction.JOIN_ACTION;
        ParticipationResponseHandler.updateCheckbox($checkbox, action, response.url);
        ParticipationResponseHandler.toggleCheckboxWrapperClasses($checkbox);

        let $slotBox = $checkbox.closest('.meal').find('.slot-selector');
        $slotBox.addClass('tmp-disabled').prop('disabled', true)
            .parent().children('.loader').css('visibility', 'visible');
    }

    public static onSuccessfulAcceptOffer($checkbox: JQuery, response: ToggleResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationResponseHandler.updateParticipantCounter(participantCounter, ParticipationState.DEFAULT, response.participantsCount);
        ParticipationResponseHandler.updateCheckbox($checkbox, ParticipationAction.SWAP, response.url, undefined)
        ParticipationResponseHandler.toggleCheckboxWrapperClasses($checkbox);
        ParticipationResponseHandler.toggleTooltip($checkbox);
    }

    public static onSuccessfulSwap($checkbox: JQuery, response: SwapResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationResponseHandler.updateParticipantCounter(participantCounter, ParticipationState.PENDING);
        ParticipationResponseHandler.updateCheckbox($checkbox, ParticipationAction.UNSWAP, response.url, response.id);

        // get text for tooltip
        let $tooltip = $checkbox.closest('.wrapper-meal-actions').find('.tooltiptext');
        $.getJSON('/labels.json')
            .done(function (data) {
                if ($('.language-switch').find('span').text() === 'de') {
                    $tooltip.text(data[1].tooltip_DE[0].offered);
                } else {
                    $tooltip.text(data[0].tooltip_EN[0].offered);
                }
            });

        ParticipationResponseHandler.toggleTooltip($checkbox);
    }

    public static onSuccessfulUnswap($checkbox: JQuery, response: SwapResponse) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        ParticipationResponseHandler.updateParticipantCounter(participantCounter, ParticipationState.DEFAULT)
        ParticipationResponseHandler.updateCheckbox($checkbox, ParticipationAction.SWAP, response.url, undefined);
        ParticipationResponseHandler.toggleTooltip($checkbox);
    }

    private static updateParticipantCounter(participantCounter: ParticipantCounter, state: ParticipationState = undefined, count: number = undefined, limit: number = undefined) {
        if ((undefined !== state) ||
            (undefined !== count && count !== participantCounter.getCount()) ||
            (undefined !== limit && limit !== participantCounter.getLimit())) {
            if (undefined !== count) participantCounter.setNextCount(count);
            if (undefined !== limit) participantCounter.setNextLimit(limit);
            if (undefined !== state) participantCounter.setNextParticipationState(state);
            participantCounter.updateUI();
        }
    }

    private static toggleCheckboxWrapperClasses($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

    private static updateCheckbox($checkbox: JQuery, checkboxClass: ParticipationAction, url: string, participantId: number = undefined) {
        $checkbox.attr('value', url);
        $checkbox.attr('class', 'participation-checkbox ' + checkboxClass);
        if (undefined === participantId) {
            $checkbox.removeData('participant-id');
        } else {
            $checkbox.data('participantId', participantId);
        }
    }

    private static toggleTooltip($checkbox: JQuery) {
        let $tooltip = $checkbox.closest('.wrapper-meal-actions').find('.tooltiptext');
        $tooltip.toggleClass('active');
    }
}