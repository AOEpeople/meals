import {ParticipantCounter, ParticipationState} from "./participant-counter";
import {ParticipationAction} from "./participation-response-handler";

export class ParticipationUpdateHandler {
    public static toggleCheckboxWrapperClasses($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

    public static toggleTooltip($checkbox: JQuery, key?: string) {
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

    public static updateCheckbox($checkbox: JQuery, checkboxClass: ParticipationAction, url: string, participantId?: number) {
        $checkbox.attr('value', url);
        $checkbox.attr('class', 'participation-checkbox ' + checkboxClass);
        if (undefined === participantId) {
            $checkbox.removeData('participant-id');
        } else {
            $checkbox.data('participantId', participantId);
        }
    }

    public static updateParticipationCounter(participantCounter: ParticipantCounter, state?: ParticipationState, count?: number, limit?: number): void {
        if ((undefined !== state) ||
            (undefined !== count && count !== participantCounter.getCount()) ||
            (undefined !== limit && limit !== participantCounter.getLimit())) {
            if (undefined !== count) participantCounter.setNextCount(count + participantCounter.getOffset());
            if (undefined !== limit) participantCounter.setNextLimit(limit);
            if (undefined !== state) participantCounter.setNextParticipationState(state);
            participantCounter.updateUI();
        }
    }
}