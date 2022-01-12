import {ParticipantCounter, ParticipationState} from "./participant-counter";
import {ParticipationAction} from "./participation-response-handler";

enum Tooltip {
    OFFERED = "offered",
    AVAILABLE = "available"
}

export class ParticipationUpdateHandler {
    private static toggleTooltip($checkbox: JQuery, key?: Tooltip) {
        let $tooltip = $checkbox.closest('.wrapper-meal-actions').find('.tooltiptext');
        if (undefined !== key) {
            $.getJSON('/labels.json')
                .done(function (data) {
                    if ($('.language-switch').find('span').text() === 'de') {
                        $tooltip.text(data[1].tooltip_DE[0][key]);
                    } else {
                        $tooltip.text(data[0].tooltip_EN[0][key]);
                    }
                });
        }
        $tooltip.toggleClass('active');
    }

    private static updateCheckboxAttributes($checkbox: JQuery, checkboxClass: ParticipationAction, url: string, participantId?: number) {
        $checkbox.attr('value', url);
        $checkbox.attr('class', 'participation-checkbox ' + checkboxClass);
        if (undefined === participantId) {
            $checkbox.removeData('participant-id');
        } else {
            $checkbox.data('participantId', participantId);
        }
    }

    private static updateCheckBoxWrapper($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

    public static updateCheckboxEnabled($checkbox: JQuery) {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);

        let checkboxEnabled = false;
        if (participantCounter.isAvailable()) {
            checkboxEnabled = !participantCounter.hasLimit() || (participantCounter.hasLimit() && (!participantCounter.isLimitReached() || $checkbox.is(':checked')));
        } else {
            checkboxEnabled =
                $checkbox.hasClass(ParticipationAction.SWAP)
                || $checkbox.hasClass(ParticipationAction.UNSWAP)
                || $checkbox.hasClass(ParticipationAction.ACCEPT_OFFER);
        }

        if (checkboxEnabled) {
            $checkbox.removeAttr('disabled');
        } else {
            $checkbox.attr('disabled', 'disabled');
        }

        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

    public static updateParticipationCounter($checkbox: JQuery, state?: ParticipationState, count?: number, limit?: number): void {
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        if ((undefined !== state) ||
            (undefined !== count && count !== participantCounter.getCount()) ||
            (undefined !== limit && limit !== participantCounter.getLimit())) {
            if (undefined !== count) participantCounter.setNextCount(count + participantCounter.getOffset());
            if (undefined !== limit) participantCounter.setNextLimit(limit);
            if (undefined !== state) participantCounter.setNextParticipationState(state);
            participantCounter.updateUI();
        }
    }

    public static changeToNextAction($checkbox: JQuery, nextAction: ParticipationAction, url: string, participantsCount: number) {
        this.updateParticipationCounter($checkbox, ParticipationState.DEFAULT, participantsCount);
        this.updateCheckboxAttributes($checkbox, nextAction, url);

        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        this.updateCheckboxEnabled($checkbox);

        let $slotBox = $checkbox.closest('.meal').find('.slot-selector');
        $slotBox.addClass('tmp-disabled').prop('disabled', true)
            .parent().children('.loader').css('visibility', 'visible');
    }

    public static changeToOfferIsTaken($checkbox: JQuery) {
        if ($checkbox.hasClass(ParticipationAction.UNSWAP)) {
            $checkbox.removeClass(ParticipationAction.UNSWAP);
            $checkbox.removeAttr('checked');
            $checkbox.attr('disabled', 'disabled');
            this.updateCheckBoxWrapper($checkbox);
            this.toggleTooltip($checkbox);
            this.updateParticipationCounter($checkbox, ParticipationState.DEFAULT);
        }
    }

    public static changeToOfferIsGone($checkbox: JQuery) {
        if ($checkbox.hasClass(ParticipationAction.ACCEPT_OFFER)) {
            $checkbox.removeClass(ParticipationAction.ACCEPT_OFFER);
            $checkbox.attr('disabled', 'disabled');
            this.updateCheckBoxWrapper($checkbox);
            this.toggleTooltip($checkbox);
            this.updateParticipationCounter($checkbox, ParticipationState.DEFAULT);
        }
    }

    public static changeToOfferIsAvailable($checkbox: JQuery, url: string) {
        $checkbox.removeAttr('disabled');
        this.updateCheckBoxWrapper($checkbox);

        this.updateCheckboxAttributes($checkbox, ParticipationAction.ACCEPT_OFFER, url);

        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        if (ParticipationState.OFFER_AVAILABLE !== participantCounter.getParticipationState()) {
            this.updateParticipationCounter($checkbox, ParticipationState.OFFER_AVAILABLE);
        }

        this.toggleTooltip($checkbox, Tooltip.AVAILABLE);
    }

    public static changeToSwapState($checkbox: JQuery, url: string, participantsCount?: number) {
        if ($checkbox.hasClass(ParticipationAction.ACCEPT_OFFER)) {
            this.updateCheckBoxWrapper($checkbox);
        }
        this.updateParticipationCounter($checkbox, ParticipationState.DEFAULT, participantsCount);
        this.updateCheckboxAttributes($checkbox, ParticipationAction.SWAP, url);
        this.toggleTooltip($checkbox);
    }

    public static changeToUnswapState($checkbox: JQuery, url: string, participantId: number) {
        this.updateParticipationCounter($checkbox, ParticipationState.PENDING);
        this.updateCheckboxAttributes($checkbox, ParticipationAction.UNSWAP, url, participantId);
        this.toggleTooltip($checkbox, Tooltip.OFFERED);
    }
}