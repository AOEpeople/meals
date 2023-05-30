import {ParticipantCounter} from './participant-counter';
import {ParticipationResponseHandler} from './participation-response-handler';
import {
    ParticipationRequest,
    ParticipationRequestHandler
} from './participation-request-handler';
import {ConfirmSwapDialog} from './confirm-swap-dialog';
import {CombinedMealService} from './combined-meal-service';

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
        const action = $checkbox.attr('data-action');
        if ('join' === action && undefined === data && $checkbox.closest('.meal').find('.slot-selector').length !== 0) {
            let slotSlug: string = $checkbox.closest('.meal').find('.slot-selector').val().toString();
            data = { 'slot': slotSlug }
        } else if ('join' === action && undefined === data) {
            data = {}
        }

        const url = $checkbox.attr('value');
        let request = new ParticipationRequest(url, data);

        switch (action) {
            case 'join':
            case 'delete':
                ParticipationRequestHandler.sendRequest(request, $checkbox, ParticipationResponseHandler.onToggle);
                break;
            case 'acceptOffer':
                ParticipationRequestHandler.sendRequest(request, $checkbox, ParticipationResponseHandler.onAcceptOffer);
                break;
            case 'rollbackOffer':
                ParticipationRequestHandler.sendRequest(request, $checkbox, ParticipationResponseHandler.onRollbackOffer);
                break;
            case 'offer':
                let dialog = new ConfirmSwapDialog({
                    participationRequest: request,
                    $checkbox: $checkbox,
                    handlerMethod: ParticipationResponseHandler.onOffer,
                });
                dialog.open();
                break;
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
        let $dishContainer = $checkbox.closest('.meal-row');
        if (CombinedMealService.isCombinedDish($dishContainer)) {
            this.updateDishSelection($checkbox, data);
        }

        $checkbox.prop('checked', !$checkbox.is(':checked'));
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
    }

    private updateDishSelection($checkbox: JQuery, data?: Array<Entry>) {
        let dishSelectionWrapperSelector = 'dish-selection-wrapper';
        let $meal = $checkbox.closest('.meal');
        let $dishContainer = $checkbox.closest('.meal-row');
        let $dishSelectionWrapper = $dishContainer.find('#' + dishSelectionWrapperSelector);
        if (0 === $dishSelectionWrapper.length) {
            $dishSelectionWrapper = $('<div id="' + dishSelectionWrapperSelector + '"></div>');
            $dishContainer.append($dishSelectionWrapper);
        } else {
            this.updateCombinedDishDesc($dishContainer, []);
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

        this.updateCombinedDishDesc($dishContainer, selectedDishes);
    }

    private updateCombinedDishDesc($dishContainer: JQuery, dishSlugs: string[]): void {
        let $dishDesc = $dishContainer.find('.description').empty();
        if (0 === dishSlugs.length) {
            $dishDesc.text($dishContainer.data('description'));
            $dishContainer.find('.title').addClass('no-description');
            return;
        }

        let $dishList = $('<div class="text dish-combination"></div>');
        dishSlugs.forEach(function (dishSlug: string) {
            $dishList.append(`<div class="dish">${dishSlug}</div>`)
        });
        $dishDesc.append($dishList);
        $dishContainer.find('.title').removeClass('no-description');
    }
}

class Entry {
    name: string;
    value: string;
}
