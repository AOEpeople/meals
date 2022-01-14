import {CombinedMealDialog, Dish, DishVariation} from "./combined-meal-dialog";
import Event = JQuery.Event;
import {AbstractParticipationToggleHandler} from "./participation-toggle-handler";
import {ParticipantCounter} from "./participant-counter";
import {CombinedMealOffersService} from "./combined-meal-offers-service";

export class ParticipationPreToggleHandler {
    private participationToggleHandler: AbstractParticipationToggleHandler;

    constructor(participationToggleHandler: AbstractParticipationToggleHandler) {
        this.participationToggleHandler = participationToggleHandler;
        this.initEvents();
    }

    private initEvents(): void {
        let self = this
        $('.checkbox-wrapper').on('click', function (e: Event) {
            let $checkboxWrapper = $(this);
            let $checkbox = $checkboxWrapper.find('input');
            if (undefined === $checkbox) {
                console.log('Error: No checkbox found');
                return;
            }

            if (self.needUserInteractionBeforeToggle($checkbox)) {
                let participantCounter: ParticipantCounter = $checkbox.data(ParticipantCounter.NAME);
                if (participantCounter.isAvailable()) {
                    self.executeBeforeToggle($checkbox);
                } else {
                    CombinedMealOffersService.execute($checkbox, self.participationToggleHandler);
                }
            } else {
                self.participationToggleHandler.toggle($checkbox);
            }
        });
    }

    private needUserInteractionBeforeToggle($checkbox: JQuery): boolean {
        return 1 === $checkbox.closest('.meal-row').data('combined') // is combined meal
            && !$checkbox.is(':checked')
            && 0 < $checkbox.closest('.meal').find('.variation-row .text-variation').length; // has variations
    }

    private executeBeforeToggle($checkbox: JQuery): void {
        let self = this;
        const slotSlug: string = $checkbox.closest('.meal').find('.slot-selector').val().toString();
        const title = $checkbox.closest('.meal-row').find('.title').text();
        const dishes = this.getCombinedMealDishes($checkbox.closest('.meal'));
        let cmd = new CombinedMealDialog(
            title,
            dishes,
            slotSlug,
            {
                ok: function (data) {
                    self.participationToggleHandler.toggle($checkbox, data);
                }
            }
        );
        cmd.open();
    }

    private getCombinedMealDishes($meal: JQuery): Dish[] {
        let dishes: Dish[] = [];
        $meal.find('.meal-row').each(function () {
            const $mealRow = $(this);
            if (1 === $mealRow.data('combined')) {
                return;
            }

            let dish: Dish = {
                title: $mealRow.find('.text .title').contents().get(0).nodeValue.trim(),
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
}