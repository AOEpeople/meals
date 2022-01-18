import {ParticipantCounter, ParticipationState} from "./participant-counter";
import {Labels, TooltipLabel} from "./labels";
import {Dish, DishVariation} from "./combined-meal-dialog";

export enum ParticipationAction {
    ACCEPT_OFFER = 'acceptOffer-action',
    DELETE = 'delete-action',
    JOIN = 'join-action',
    SWAP = 'swap-action',
    UNSWAP = 'unswap-action',
}

export class ParticipationUpdateHandler {
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

    private static updateCheckBoxWrapper($checkbox: JQuery) {
        let $checkboxWrapper = $checkbox.closest('.checkbox-wrapper');
        $checkboxWrapper.toggleClass('checked', $checkbox.is(':checked'));
        $checkboxWrapper.toggleClass('disabled', $checkbox.is(':disabled'));
    }

    private static updateCheckboxEnabled($checkbox: JQuery) {
        let checkboxEnabled = false;
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        if (participantCounter.isAvailable()) {
            checkboxEnabled = !participantCounter.hasLimit()
                || (participantCounter.hasLimit() && ((participantCounter.isLimitReached() && $checkbox.is(':checked')) || !participantCounter.isLimitReached()));
        } else {
            checkboxEnabled =
                $checkbox.hasClass(ParticipationAction.SWAP)
                || $checkbox.hasClass(ParticipationAction.UNSWAP)
                || $checkbox.hasClass(ParticipationAction.ACCEPT_OFFER);
        }

        $checkbox.prop('disabled', !checkboxEnabled);
    }

    private static changeCheckboxAttributes($checkbox: JQuery, checkboxClass: ParticipationAction, url: string, participantId?: number) {
        $checkbox.attr('value', url);
        $checkbox.attr('class', 'participation-checkbox ' + checkboxClass);
        if (undefined === participantId) {
            $checkbox.removeData('participant-id');
        } else {
            $checkbox.data('participantId', participantId);
        }
    }

    private static changeParticipationCounter($checkbox: JQuery, state?: ParticipationState, count?: number, limit?: number): void {
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

    public static updateParticipation($checkbox: JQuery, count: number, limit: number) {
        // change
        this.changeParticipationCounter($checkbox, undefined, count, limit);

        // update
        this.updateCheckboxEnabled($checkbox);
        this.updateCheckBoxWrapper($checkbox);
    }

    public static toggleAction($checkbox: JQuery, actionText: string, url: string, participantsCount: number, bookedDishIDs: string[]) {
        // change
        $checkbox.prop('checked', !$checkbox.is(':checked'));
        let nextAction = ('deleted' === actionText) ? ParticipationAction.JOIN : ParticipationAction.DELETE;
        this.changeCheckboxAttributes($checkbox, nextAction, url);
        this.changeParticipationCounter($checkbox, ParticipationState.DEFAULT, participantsCount);

        // update
        this.updateCheckboxEnabled($checkbox);
        this.updateCheckBoxWrapper($checkbox);

        let $mealContainer = $checkbox.closest('.meal');
        let $slotBox = $mealContainer.find('.slot-selector');
        $slotBox.addClass('tmp-disabled').prop('disabled', true)
            .parent().children('.loader').css('visibility', 'visible');

        ParticipationUpdateHandler.updateCombinedDish($checkbox, bookedDishIDs);
    }

    public static changeToOfferIsTaken($checkbox: JQuery) {
        if ($checkbox.hasClass(ParticipationAction.UNSWAP)) {
            // change
            $checkbox.removeClass(ParticipationAction.UNSWAP);
            $checkbox.prop('checked', false);
            this.changeParticipationCounter($checkbox, ParticipationState.DEFAULT);

            // update
            this.updateCheckboxEnabled($checkbox);
            this.updateCheckBoxWrapper($checkbox);
            this.toggleTooltip($checkbox);
        }
    }

    public static changeToOfferIsGone($checkbox: JQuery) {
        if ($checkbox.hasClass(ParticipationAction.ACCEPT_OFFER)) {
            // change
            $checkbox.removeClass(ParticipationAction.ACCEPT_OFFER);
            this.changeParticipationCounter($checkbox, ParticipationState.DEFAULT);

            // update
            this.updateCheckboxEnabled($checkbox);
            this.updateCheckBoxWrapper($checkbox);
            this.toggleTooltip($checkbox);
        }
    }

    public static changeToOfferIsAvailable($checkbox: JQuery, url: string) {
        // change
        this.changeCheckboxAttributes($checkbox, ParticipationAction.ACCEPT_OFFER, url);
        let participantCounter = $checkbox.data(ParticipantCounter.NAME);
        if (ParticipationState.OFFER_AVAILABLE !== participantCounter.getParticipationState()) {
            this.changeParticipationCounter($checkbox, ParticipationState.OFFER_AVAILABLE);
        }

        // update
        this.updateCheckboxEnabled($checkbox);
        this.updateCheckBoxWrapper($checkbox);
        this.toggleTooltip($checkbox, TooltipLabel.AVAILABLE_MEAL);
    }

    public static changeToSwapState($checkbox: JQuery, url: string, participantsCount?: number) {
        // change
        $checkbox.prop('checked', true);
        this.changeCheckboxAttributes($checkbox, ParticipationAction.SWAP, url);
        this.changeParticipationCounter($checkbox, ParticipationState.DEFAULT, participantsCount);

        // update
        this.updateCheckboxEnabled($checkbox);
        this.updateCheckBoxWrapper($checkbox);
        this.toggleTooltip($checkbox);
    }

    public static changeToUnswapState($checkbox: JQuery, url: string, participantId: number) {
        // change
        $checkbox.prop('checked', true);
        this.changeCheckboxAttributes($checkbox, ParticipationAction.UNSWAP, url, participantId);
        this.changeParticipationCounter($checkbox, ParticipationState.PENDING);

        // update
        this.updateCheckboxEnabled($checkbox);
        this.updateCheckBoxWrapper($checkbox);
        this.toggleTooltip($checkbox, TooltipLabel.OFFERED_MEAL);
    }

    private static getCombinedMealDishes($meal: JQuery): Dish[] {
        let dishes: Dish[] = [];
        $meal.find('.meal-row').each(function () {
            const $mealRow = $(this);
            if (1 === $mealRow.data('combined')) {
                return;
            }

            let dish: Dish = {
                title: $mealRow.find('.title').contents().get(0).nodeValue.trim(),
                slug: $mealRow.data('slug'),
                variations: []
            };
            $mealRow.find('.variation-row').each(function () {
                const $dishVarRow = $(this);
                let dishVariation: DishVariation = {
                    title: $dishVarRow.find('.text-variation').text().trim(),
                    slug: $dishVarRow.data('slug')
                };
                dish.variations.push(dishVariation);
            });
            dishes.push(dish);
        });

        return dishes;
    }

    /**
     * @param $checkbox     Combined Dish Checkbox
     * @param bookedDishIDs Dish IDs in booked combined meal
     */
    private static updateCombinedDish($checkbox: JQuery, bookedDishIDs: string[]) {
        let $dishContainer = $checkbox.closest('.meal-row');

        if (Array.isArray(bookedDishIDs) && (0 < bookedDishIDs.length)) {
            let $mealContainer = $dishContainer.closest('.meal');
            const dishes = ParticipationUpdateHandler.getCombinedMealDishes($mealContainer);
            let dt = ParticipationUpdateHandler.getBookedDishTitles(bookedDishIDs, dishes);
            if (0 < dt.length) {
                // update dish description with titles of booked dishes
                const bookedDishTitles = dt.map(dishTitle => $(`<div class="dish">${dishTitle}</div>`));
                $dishContainer.find('.description .dish-combination').empty().append(...bookedDishTitles);
                $dishContainer.find('.title').addClass('edit');
                // update booked dish IDs in data attribute
                $dishContainer.attr('data-booked-dishes', bookedDishIDs.join(','));
            }

            return;
        }

        let desc = $dishContainer.data('description');
        $dishContainer.find('.description .dish-combination').empty().text(desc);
        $dishContainer.find('.title').removeClass('edit');
        $dishContainer.attr('data-booked-dishes', '');
    }

    private static getBookedDishTitles(dishIDs: string[], dishes: Dish[]|DishVariation[]) {
        let dishTitles: string[] = [];
        dishes.forEach(function(dish){
            let idx = dishIDs.indexOf(dish.slug);
            if (-1 < idx) {
                dishTitles.push(dish.title);
                dishIDs.slice(idx, 1);
            } else if (Array.isArray(dish.variations) && 0 < dish.variations.length) {
                let dvt = ParticipationUpdateHandler.getBookedDishTitles(dishIDs, dish.variations);
                dishTitles.push(...dvt);
            }
        });

        return dishTitles;
    }
}
