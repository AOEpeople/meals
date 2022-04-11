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
    available: boolean
}

export interface ParticipationUpdateData {
    mealId: number;
    count: number;
    available: boolean;
    availableWith?: string[];
    locked: boolean;
}

interface CheckboxAttributes {
    value: string;
    checked: boolean;
    disabled: boolean;
    action: string;
    participantID: number;
}

interface Status {
    state: State;
    action?: string;
    actionURL?: string;
    count?: number;
    participantID?: number;
}

export class ParticipationUpdateHandler {
    /**
     * Handles event when a user successfully accepts an offered meal.
     *
     * @param $checkbox Meal Checkbox
     * @param data      Server response
     */
    public static acceptOffer($checkbox: JQuery, data: AcceptOfferData): void {
        this.updateStatus($checkbox, {
            state: State.BOOKED_AND_CLOSED,
            action: 'offer',
            actionURL: data.url
        });
        this.toggleTooltip($checkbox);

        let $dishContainer = $checkbox.closest('.meal-row');
        if (1 > $dishContainer.length) {
            return;
        }

        if (CombinedMealService.isCombinedDish($dishContainer)) {
            CombinedMealService.updateDishes($checkbox, data.participantID, data.bookedDishSlugs);
        }
    }

    public static updateParticipation(data: ParticipationUpdateData[]) {
        for (const [mealId, update] of Object.entries(data)) {
            let $checkbox = $(`div[data-id=${mealId}] input[type=checkbox]`);
            if (1 > $checkbox.length) {
                console.error(`checkbox not found, mealId: ${mealId}`);
                continue;
            }

            const state = ParticipationUpdateHandler.getNextState(
                $checkbox,
                $checkbox.prop('checked'),
                update.available,
                update.locked
            );
            ParticipationUpdateHandler.updateStatus($checkbox, {
                state: state,
                count: update.count
            });

            let $mealContainer = $checkbox.closest('.meal-row');
            if (1 > $mealContainer.length) {
                continue;
            }

            if (update.availableWith === undefined) {
                $mealContainer.attr('data-available-dishes', '');
            } else {
                $mealContainer.attr('data-available-dishes', update.availableWith.join(','))
            }
        }
    }

    public static changeToAssignedSlot($checkbox: JQuery, slot: string){
        let $assignedSlot = $checkbox.closest('.meal').find('[value='+ slot +']');
        $assignedSlot.prop('selected', 'selected');
    }

    /**
     * Handles event when a meal is offered to be taken over by other users.
     *
     * @param $checkbox Meal Checkbox
     * @param url       URL to send the accept-meal request
     */
    public static changeToOfferIsAvailable($checkbox: JQuery, url: string) {
        this.updateStatus($checkbox, {
            state: State.OPEN,
            action: 'acceptOffer',
            actionURL: url
        });
        this.toggleTooltip($checkbox, TooltipLabel.AVAILABLE_MEAL);
    }

    /**
     * Handles event when an offered meal is accepted by some user.
     *
     * @param $checkbox Meal Checkbox
     * @param offererId Participant-ID of the accepted meal
     * @param available Weather or not the accepted/taken meal is still available, i.e. still being offered
     */
    public static offerAccepted($checkbox: JQuery, offererId: number, available: boolean = false) {
        let $dishContainer = $checkbox.closest('.meal-row');
        if (1 > $dishContainer.length) {
            console.error('dish container not found');
            return;
        }

        // unset OFFERED state if participant-ID and offerer-ID are same, i.e. current user's meal got accepted
        const participantId = Number($dishContainer.attr('data-id'));
        if (offererId === participantId) {
            if (available) {
                ParticipationUpdateHandler.updateStatus($checkbox, {
                    state: State.OPEN,
                    action: 'acceptOffer',
                    actionURL: ParticipationUpdateHandler.getAcceptOfferURL($checkbox),
                    participantID: null
                });
            } else {
                ParticipationUpdateHandler.updateStatus($checkbox, { state: State.CLOSED });
            }

            return;
        }

        if (CombinedMealService.isCombinedDish($dishContainer)) {
            CombinedMealService.updateDishes($checkbox, undefined, []);
        }

        this.toggleTooltip($checkbox);
    }

    public static changeToOfferIsGone($checkbox: JQuery) {
        const nextState = ParticipationUpdateHandler.getNextState(
            $checkbox,
            $checkbox.prop('checked'),
            false,
            true
        );
        this.updateStatus($checkbox, {state: nextState});
    }

    public static rollbackOffer($checkbox: JQuery, url: string) {
        this.updateStatus($checkbox, {
            state: State.BOOKED_AND_CLOSED,
            action: 'offer',
            actionURL: url
        });
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
        this.updateStatus($checkbox, {
            state: State.OFFERED,
            action: 'rollbackOffer',
            actionURL: url,
            participantID: participantId
        });
        this.toggleTooltip($checkbox, TooltipLabel.OFFERED_MEAL);
    }

    public static toggle($checkbox: JQuery, data: ToggleData): void {
        const nextAction = ('deleted' === data.actionText) ? 'join' : 'delete';
        const state = ParticipationUpdateHandler.getNextState(
            $checkbox,
            !$checkbox.prop('checked'),
            data.available,
            false
        );
        ParticipationUpdateHandler.updateStatus($checkbox, {
            state: state,
            count: data.participantsCount,
            action: nextAction,
            actionURL: data.url
        });

        if ('added' === data.actionText && data.slot !== '') {
            ParticipationUpdateHandler.changeToAssignedSlot($checkbox, data.slot);
        }

        let $dishContainer = $checkbox.closest('.meal-row');
        if (1 > $dishContainer.length) {
            return;
        }

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

    /**
     * @param $checkbox Meal Checkbox
     * @param check     Weather or not the meal checkbox be checked
     * @param available Weather or not the meal is available
     * @param locked    Weather or not the meal is locked
     * @private
     */
    private static getNextState($checkbox: JQuery, check: boolean, available: boolean, locked: boolean): State {
        if (available) {
            // no participation, and meal is not locked
            if (!check && !locked) {
                return State.OPEN;
            }
            // no participation, and meal is locked
            if (!check && locked) {
                return State.CLOSED;
            }
            // participation, and meal is locked
            if (check && locked) {
                return ('rollbackOffer' === $checkbox.attr('data-action')) ? State.OFFERED : State.BOOKED_AND_CLOSED;
            }
            if (check && !locked) {
                return State.BOOKED;
            }
        }

        // no participation, and meal is not available
        if (!check) {
            return State.CLOSED;
        }

        // participation, and meal is not available
        return State.BOOKED_AND_CLOSED;
    }

    private static updateStatus($checkbox: JQuery, status: Status): void {
        const state = status.state;
        const checked = state === State.BOOKED || state === State.BOOKED_AND_CLOSED || state === State.OFFERED;
        const disabled = state === State.CLOSED;

        let attrs: Partial<CheckboxAttributes> = {
            checked: checked,
            disabled: disabled,
        };

        if (State.CLOSED === state) {
            attrs.action = null;
            attrs.value = '';
            attrs.participantID = null;
        } else {
            attrs.action = status.action;
            attrs.value = status.actionURL;
            attrs.participantID = status.participantID;
        }

        ParticipationUpdateHandler.updateCheckboxAttributes($checkbox, attrs);
        this.updateCheckboxWrapper($checkbox);

        ParticipationUpdateHandler.setCount($checkbox, state, status.count);
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
     * @param $checkbox Meal Checkbox
     * @param attrs     Checkbox attributes
     * @private
     */
    private static updateCheckboxAttributes($checkbox: JQuery, attrs: Partial<CheckboxAttributes>) {
        if (undefined !== attrs.checked) {
            $checkbox.prop('checked', attrs.checked).trigger('change');
            if (false === attrs.checked) {
                $checkbox.removeAttr('checked');
            }
        }
        if (undefined !== attrs.disabled) {
            $checkbox.prop('disabled', attrs.disabled);
            if (false === attrs.disabled) {
                $checkbox.removeAttr('disabled');
            }
        }
        if (undefined !== attrs.action) {
            if (null === attrs.action) {
                $checkbox.removeAttr('data-action');
            } else {
                $checkbox.attr('data-action', attrs.action);
            }
        }
        if (undefined !== attrs.value) {
            $checkbox.attr('value', attrs.value);
        }

        if (undefined !== attrs.participantID) {
            if (null === attrs.participantID) {
                $checkbox.removeAttr('data-participant-id');
            } else {
                $checkbox.attr('data-participant-id', attrs.participantID);
            }
        }
    }
}
