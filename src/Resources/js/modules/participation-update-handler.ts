import {Labels, TooltipLabel} from "./labels";
import {CombinedMealService} from "./combined-meal-service";

/**
 * Meal States
 */
export enum State {
    OPEN = 1,                // Open for participation
    CLOSED,             // Closed for participation
    BOOKED,             // Booked; further participation is possible
    BOOKED_AND_CLOSED,  // Booked; further participation is not possible
    OFFERED,            // Booked and offered; further participation is not possible
}

export interface AcceptOfferData {
    participantID: number
    url: string;
    participantsCount: number;
    bookedDishSlugs: string[];
}

export interface ToggleData extends AcceptOfferData {
    actionText: string;
    slot: string;
}

export interface ParticipationUpdateData {
    mealId: number;
    count: number;
    available: boolean;
    availableWith?: string[];
    locked: boolean;
}

export class ParticipationUpdateHandler {

    public static acceptOffer($checkbox: JQuery, data: AcceptOfferData): void {
        this.rollbackOffer($checkbox, data.url);

        let $dishContainer = $checkbox.closest('.meal-row');
        if (CombinedMealService.isCombinedDish($dishContainer)) {
            CombinedMealService.updateDishes($checkbox, data.participantID, data.bookedDishSlugs);
        }
    }

    public static updateParticipation(data: ParticipationUpdateData[]) {
        for (const [mealId, update] of Object.entries(data)) {
            let $checkbox = $(`div[data-id=${mealId}] input[type=checkbox]`);
            if (1 > $checkbox.length) {
                continue;
            }

            const state = ParticipationUpdateHandler.getState($checkbox, update.available, update.locked);
            ParticipationUpdateHandler.updateStatus($checkbox, state, update.count);

            if (update.availableWith === undefined) {
                continue;
            }

            let $mealContainer = $checkbox.closest('.meal-row');
            if (1 > $mealContainer.length) {
                continue;
            }

            $mealContainer.attr('data-available-dishes', update.availableWith.join(','))
        }
    }

    public static changeToAssignedSlot($checkbox: JQuery, slot: string){
        let $assignedSlot = $checkbox.closest('.meal').find('[value='+ slot +']');
        $assignedSlot.prop('selected', 'selected');
    }

    public static changeToOfferIsAvailable($checkbox: JQuery, url: string) {
        this.updateCheckboxAttributes($checkbox, 'acceptOffer', url);
        this.updateStatus($checkbox, State.OPEN);
        this.toggleTooltip($checkbox, TooltipLabel.AVAILABLE_MEAL);
    }

    /**
     * @param $checkbox Meal Checkbox
     * @param available Is the accepted/taken meal is still available, i.e. still being offered.
     */
    public static changeToOfferIsTaken($checkbox: JQuery, available: boolean = false) {
        let nextState = State.CLOSED;

        if (available) {
            nextState = State.OPEN;
            const acceptOfferURL = ParticipationUpdateHandler.getAcceptOfferURL($checkbox);
            this.updateCheckboxAttributes($checkbox, 'acceptOffer', acceptOfferURL)
        } else {
            this.updateCheckboxAttributes($checkbox)
        }

        this.updateStatus($checkbox, nextState);

        if (CombinedMealService.isCombinedDish($checkbox)) {
            CombinedMealService.updateDishes($checkbox, undefined, []);
        }

        this.toggleTooltip($checkbox);
    }

    public static changeToOfferIsGone($checkbox: JQuery) {
        const state = ParticipationUpdateHandler.getState($checkbox, false, true);

        if (State.CLOSED === state) {   // clear previously set accept meal attributes
            ParticipationUpdateHandler.updateCheckboxAttributes($checkbox);
        }

        this.updateStatus($checkbox, state);
    }

    public static rollbackOffer($checkbox: JQuery, url: string) {
        this.updateCheckboxAttributes($checkbox, 'offer', url);
        this.updateStatus($checkbox, State.BOOKED_AND_CLOSED);
        this.toggleTooltip($checkbox);
    }

    /**
     * Mark meal as offered.
     *
     * @param $checkbox     Meal Checkbox
     * @param url           Offer Rollback URL
     * @param participantId Meal participant ID
     */
    public static setOffered($checkbox: JQuery, url: string, participantId: number) {
        this.updateCheckboxAttributes($checkbox, 'rollbackOffer', url, participantId);
        this.updateStatus($checkbox, State.OFFERED);
        this.toggleTooltip($checkbox, TooltipLabel.OFFERED_MEAL);
    }

    public static toggle($checkbox: JQuery, data: ToggleData): void {
        const nextAction = ('deleted' === data.actionText) ? 'join' : 'delete';
        ParticipationUpdateHandler.updateCheckboxAttributes($checkbox, nextAction, data.url);

        const state = (nextAction === 'join') ? State.OPEN : State.BOOKED;
        ParticipationUpdateHandler.updateStatus($checkbox, state, data.participantsCount);

        if ('added' === data.actionText && data.slot !== '') {
            ParticipationUpdateHandler.changeToAssignedSlot($checkbox, data.slot);
        }

        let $dishContainer = $checkbox.closest('.meal-row');
        if (CombinedMealService.isCombinedDish($dishContainer)) {
            CombinedMealService.updateDishes($checkbox, data.participantID, data.bookedDishSlugs);
        }
    }

    private static getAcceptOfferURL($checkbox: JQuery): string {
        const date = $checkbox.closest('[data-date]').data('date');
        if (undefined === date || '' === date) {
            return '';
        }

        const dishSlug = $checkbox.closest('[data-slug]').data('slug');
        if (undefined === dishSlug || '' === dishSlug) {
            return '';
        }

        return `/menu/${date}/${dishSlug}/accept-offer`;
    }

    private static getState($checkbox: JQuery, available: boolean, locked: boolean): State {
        if (available) {
            // no participation, and meal is not locked
            if (!$checkbox.is(':checked') && !locked) {
                return State.OPEN;
            }
            // no participation, and meal is locked
            if (!$checkbox.is(':checked') && locked) {
                return State.CLOSED;
            }
            // participation and meal is locked
            if ($checkbox.is(':checked') && locked) {
                return State.BOOKED_AND_CLOSED;
            }
            if ($checkbox.is(':checked') && !locked) {
                return State.BOOKED;
            }
        }

        // no participation and meal is not available
        if (!$checkbox.is(':checked')) {
            return State.CLOSED;
        }

        // participation; meal is locked and offered
        if (locked && ('rollbackOffer' === $checkbox.attr('data-action'))) {
            return State.OFFERED;
        }

        // participation and meal is not available
        return State.BOOKED_AND_CLOSED;
    }

    private static updateStatus($checkbox: JQuery, state: State, count?: number): void {
        ParticipationUpdateHandler.setState($checkbox, state);
        ParticipationUpdateHandler.setCount($checkbox, state, count);
    }

    private static setCount($checkbox: JQuery, state: State, count?: number): void {
        let $countContainer = $checkbox.closest('.wrapper-meal-actions').find('.participants-count');
        if (1 > $countContainer.length) {
            return;
        }

        $countContainer.removeClass().addClass('participants-count');

        switch (state) {
            case State.OPEN:
            case State.BOOKED:
                $countContainer.addClass('participation-allowed');
                break;
            case State.OFFERED:
                $countContainer.addClass('participation-pending');
                break;
        }

        if (undefined === count) {
            return;
        }

        let partCount = $countContainer.find('.count');
        if (0 < partCount.length) {
            partCount.text(count);
        }
    }

    private static setState($checkbox: JQuery, state: State): void {
        const checked = state === State.BOOKED || state === State.BOOKED_AND_CLOSED || state === State.OFFERED;
        const disabled = state === State.CLOSED;
        $checkbox
            .prop('checked', checked)
            .trigger('change')
            .prop('disabled', disabled);

        if (!checked) {
            $checkbox.removeAttr('checked');
        }
        if (!disabled) {
            $checkbox.removeAttr('disabled');
        }

        this.updateCheckboxWrapper($checkbox);
    }

    private static toggleTooltip($checkbox: JQuery, label?: TooltipLabel) {
        let $tooltip = $checkbox.closest('.wrapper-meal-actions').find('.tooltiptext');
        if (undefined !== label) {
            $.getJSON('/labels.json')
                .done(function (labels: Labels) {
                    if ('de' === $('.language-switch').find('span').text()) {
                        $tooltip.text(labels.de.tooltip[label]);
                    } else {
                        $tooltip.text(labels.en.tooltip[label]);
                    }
                    $tooltip.toggleClass('active');
                });
        } else {
            $tooltip.toggleClass('active');
        }
    }

    private static updateCheckboxWrapper($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

    /**
     * @param $checkbox     Meal Checkbox
     * @param action        Action performed on next state change of checkbox
     * @param value         Checkbox value
     * @param participantId Participant-ID
     * @private
     */
    private static updateCheckboxAttributes($checkbox: JQuery, action?: string, value?: string, participantId?: number) {
        if (undefined === action) {
            $checkbox.removeAttr('data-action');
        } else {
            $checkbox.attr('data-action', action);
        }

        $checkbox.attr('value', (undefined === value) ? '' : value);

        if (undefined === participantId) {
            $checkbox.removeAttr('data-participant-id');
        } else {
            $checkbox.attr('data-participant-id', participantId);
        }
    }
}
