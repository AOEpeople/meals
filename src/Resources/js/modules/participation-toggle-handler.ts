import {ParticipantCounter} from "./participant-counter";
import {ParticipationAction, ParticipationResponseHandler} from "./participation-response-handler";
import {
    JoinParticipationRequest,
    ParticipationRequest,
    ParticipationRequestHandler
} from "./participation-request-handler";
import {ConfirmSwapDialog} from "./confirm-swap-dialog";

export abstract class AbstractParticipationToggleHandler {
    protected readonly checkboxWrapperClass = 'checkbox-wrapper';

    constructor($checkboxes: JQuery) {
        this.prepare($checkboxes);
        this.initEvents($checkboxes);
    }

    private prepare($checkboxes: JQuery): void {
        let self = this;
        $checkboxes.each(function (idx, checkbox) {
            let $checkbox = $(checkbox);
            let $actionsWrapper = $checkbox.closest(ParticipantCounter.PARENT_WRAPPER_CLASS);
            $checkbox.data(ParticipantCounter.NAME, new ParticipantCounter($actionsWrapper));
            self.toggleCheckboxWrapperClasses($checkbox);
        });
    }

    private initEvents($checkboxes: JQuery): void {
        $('.' + this.checkboxWrapperClass + ' input').on('click', function (e) {
            e.stopPropagation();
        });

        let self = this;
        $checkboxes.on('change', function () {
            self.toggle($(this));
        });
    }

    protected abstract toggle($checkbox: JQuery): void;

    // TODO make it protected after better integration of CombinedMealDialog
    public abstract toggleCheckboxWrapperClasses($checkbox: JQuery): void;
}

export class ParticipationToggleHandler extends AbstractParticipationToggleHandler {
    protected toggle($checkbox: JQuery) {
        if (undefined === $checkbox) {
            console.log('Error: No checkbox found');
            return;
        }

        let participationRequest;
        if ($checkbox.hasClass(ParticipationAction.JOIN_ACTION)) {
            participationRequest = new JoinParticipationRequest($checkbox);
        } else {
            participationRequest = new ParticipationRequest($checkbox);
        }

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

    public toggleCheckboxWrapperClasses($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.' + this.checkboxWrapperClass);
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }
}

export class ParticipationGuestToggleHandler extends AbstractParticipationToggleHandler {
    // TODO remove this after better integration of CombinedMealDialog
    public toggleGuest($checkbox: JQuery) {
        this.toggle($checkbox);
    }

    protected toggle($checkbox: JQuery) {
        this.toggleCheckboxWrapperClasses($checkbox);
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        if ((!participantCounter.hasOffset() && $checkbox.is(':checked')) ||
            (participantCounter.hasOffset() && !$checkbox.is(':checked'))) {
            participantCounter.toggleOffset();
            participantCounter.updateUI();
        }

        if (1 === $checkbox.parents('.meal-row').data('combined') && !$checkbox.is(':checked')) {
            this.updateDishSelection($checkbox, []);
        }
    }

    // TODO make it private after better integration of CombinedMealDialog
    public updateDishSelection($checkbox: JQuery, data: Array<Entry>) {
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

    public toggleCheckboxWrapperClasses($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.' + this.checkboxWrapperClass);
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        if ($checkboxWrapper.hasClass('disabled')) {
            $checkbox.closest('input').attr('disabled', 'disabled');
        }
    }
}

class Entry {
    name: string;
    value: string;
}