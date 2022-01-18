import {ParticipantCounter} from "./participant-counter";
import {ParticipationResponseHandler} from "./participation-response-handler";
import {
    ParticipationRequest,
    ParticipationRequestHandler
} from "./participation-request-handler";
import {ConfirmSwapDialog} from "./confirm-swap-dialog";
import {ParticipationAction} from "./participation-update-handler";

export abstract class AbstractParticipationToggleHandler {
    constructor($checkboxes: JQuery) {
        this.prepare($checkboxes);
        this.initEvents($checkboxes);
    }

    public abstract toggle($checkbox: JQuery, data?: {}): void;

    protected initCheckboxState($checkbox: JQuery): void {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
    }

    private prepare($checkboxes: JQuery): void {
        let self = this;
        $checkboxes.each(function (idx, checkbox) {
            let $checkbox = $(checkbox);
            let $actionsWrapper = $checkbox.closest('.wrapper-meal-actions');
            $checkbox.data(ParticipantCounter.NAME, new ParticipantCounter($actionsWrapper));
            self.initCheckboxState($checkbox);
        });
    }

    private initEvents($checkboxes: JQuery): void {
        $checkboxes.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
    }
}

export class ParticipationToggleHandler extends AbstractParticipationToggleHandler {
    public toggle($checkbox: JQuery, data?: {}) {
        let participationRequest;
        let requestData = data;
        const url = $checkbox.attr('value');

        if ($checkbox.hasClass(ParticipationAction.JOIN) && undefined === requestData) {
            let slotSlug: string = $checkbox.closest('.meal').find('.slot-selector').val().toString();
            requestData = {
                'slot': slotSlug
            }
        }

        participationRequest = new ParticipationRequest(url, requestData);

        let handlerMethod;
        if ($checkbox.hasClass(ParticipationAction.SWAP)) {
            handlerMethod = ParticipationResponseHandler.onSuccessfulSwap;
            let csd = new ConfirmSwapDialog(
                {
                    participationRequest,
                    $checkbox,
                    handlerMethod
                }
            );

            csd.open();
        } else {
            if ($checkbox.hasClass(ParticipationAction.UNSWAP)) {
                handlerMethod = ParticipationResponseHandler.onSuccessfulUnswap;
            } else if ($checkbox.hasClass(ParticipationAction.ACCEPT_OFFER)) {
                handlerMethod = ParticipationResponseHandler.onSuccessfulAcceptOffer;
            } else { // JOIN or DELETE
                handlerMethod = ParticipationResponseHandler.onSuccessfulToggle;
            }

            ParticipationRequestHandler.sendRequest(participationRequest, $checkbox, handlerMethod);
        }
    }

    protected initCheckboxState($checkbox: JQuery): void {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }
}

export class ParticipationGuestToggleHandler extends AbstractParticipationToggleHandler {
    public toggle($checkbox: JQuery, data?: any) {
        if (1 === $checkbox.closest('.meal-row').data('combined')) {
            this.updateDishSelection($checkbox, data);
        }

        $checkbox.prop('checked', !$checkbox.is(':checked'));
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));

        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        if ((!participantCounter.hasOffset() && $checkbox.is(':checked')) ||
            (participantCounter.hasOffset() && !$checkbox.is(':checked'))) {
            participantCounter.toggleOffset();
            participantCounter.updateUI();
        }
    }

    private updateDishSelection($checkbox: JQuery, data?: Array<Entry>) {
        let dishSelectionWrapperSelector = 'dish-selection-wrapper';
        let $meal = $checkbox.closest('.meal');
        let $textWrapper = $checkbox.closest('.meal-row').find('.text');
        let $dishSelectionWrapper = $textWrapper.find('#' + dishSelectionWrapperSelector);
        if (0 === $dishSelectionWrapper.length) {
            $dishSelectionWrapper = $('<div id="' + dishSelectionWrapperSelector + '"></div>');
            $textWrapper.append($dishSelectionWrapper);
        } else {
            $dishSelectionWrapper.empty();
        }

        if (undefined === data) {
            return;
        }

        let selectedDishes: Array<string> = new Array<string>();
        data.filter(entry => entry.name.startsWith('dishes')).forEach(entry => {
            let $dishSelectionField = '<input type="hidden" name="' + entry.name + '" value="' + entry.value + '">';
            $dishSelectionWrapper.append($dishSelectionField);

            let $mealWrapper = $meal.find('[data-slug="' + entry.value + '"]');
            let $dishTitle;
            if ($mealWrapper.hasClass('meal-row')) {
                $dishTitle = $mealWrapper.find('.title');
            } else if ($mealWrapper.hasClass('variation-row')) {
                $dishTitle = $mealWrapper.find('.text-variation');
            }
            selectedDishes.push($dishTitle.text());
        });

        $dishSelectionWrapper.append(selectedDishes.join(', '))
    }
}

class Entry {
    name: string;
    value: string;
}
