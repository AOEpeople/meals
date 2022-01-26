import {CombinedMealDialog, SerializedFormData} from "./combined-meal-dialog";
import Event = JQuery.Event;
import {AbstractParticipationToggleHandler} from "./participation-toggle-handler";
import {ParticipantCounter} from "./participant-counter";
import {CombinedMealOffersService} from "./combined-meal-offers-service";
import {CombinedMealService} from "./combined-meal-service";

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
            } else if (self.isCombinedMealParticipation($checkbox)) {
                let $mealContainer = $checkbox.closest('.meal');
                let simpleDishSlugs = self.getSimpleDishSlugs($mealContainer);
                if (0 === simpleDishSlugs.length) {
                    console.log('combined-meal dishes not found');
                    return;
                }
                let data: SerializedFormData[] = [];
                simpleDishSlugs.forEach(function (slug, i) {
                    data.push({
                        'name': `dishes[${i}]`,
                        'value': slug
                    });
                });
                self.participationToggleHandler.toggle($checkbox, data);
            } else {
                self.participationToggleHandler.toggle($checkbox);
            }
        });
    }

    private needUserInteractionBeforeToggle($checkbox: JQuery): boolean {
        return this.isCombinedMealParticipation($checkbox)
            && !$checkbox.is(':checked')
            && 0 < $checkbox.closest('.meal').find('.variation-row .text-variation').length; // has variations
    }

    private isCombinedMealParticipation($checkbox: JQuery): boolean {
        return 0 < $checkbox.closest('.meal-row.combined-meal').length;
    }

    private getSimpleDishSlugs($mealContainer: JQuery): string[]
    {
        let dishes: string[] = [];
        $mealContainer
            .find('.meal-row:not(.combined-meal)')
            .each(function() {
                dishes.push($(this).data('slug'));
            });

        return dishes;
    }

    public executeBeforeToggle($checkbox: JQuery): void {
        let self = this;
        let $dishContainer = $checkbox.closest('.meal-row');
        let $mealContainer = $dishContainer.closest('.meal');

        const slotSlug: string = $mealContainer.find('.slot-selector').val().toString();
        const title = $dishContainer.find('.title').text();
        const dishes = CombinedMealService.getCombinedMealDishes($mealContainer);
        const $bookedDishIDs = $dishContainer.data('bookedDishes').split(',').map((id: string) => id.trim());
        let cmd = new CombinedMealDialog(
            title,
            dishes,
            $bookedDishIDs,
            slotSlug,
            {
                ok: function (data) {
                    self.participationToggleHandler.toggle($checkbox, data);
                }
            }
        );
        cmd.open();
    }
}
