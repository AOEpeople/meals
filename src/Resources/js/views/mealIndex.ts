import {ParticipationPreToggleHandler} from "../modules/participation-pre-toggle-handler";
import {ParticipationToggleHandler} from "../modules/participation-toggle-handler";
import {ParticipationCountUpdateHandler} from "../modules/participation-count-update-handler";
import {CombinedMealDialog, Dish, DishVariation} from "../modules/combined-meal-dialog";
import {ParticipationRequest, ParticipationRequestHandler} from "../modules/participation-request-handler";

export default class MealIndexView {
    participationPreToggleHandler: ParticipationPreToggleHandler;
    readonly participationCheckboxSelector: string = '.meals-list .meal .participation-checkbox';

    constructor() {
        this.updateSlots();
        setInterval(this.updateSlots, 3000);

        this.initEvents();

        if ($(this.participationCheckboxSelector).length > 0) {
            console.log($(this.participationCheckboxSelector).length);
            let $participationCheckboxes = $(this.participationCheckboxSelector);
            let participationToggleHandler = new ParticipationToggleHandler($participationCheckboxes);
            let participationCountUpdateHandler = new ParticipationCountUpdateHandler($participationCheckboxes);
            this.participationPreToggleHandler = new ParticipationPreToggleHandler(participationToggleHandler);
        } else {
            console.log($(this.participationCheckboxSelector).length);
        }
    }

    private initEvents(): void {
        // set handler for slot change event
        $('.meals-list .meal .slot-selector').on('change', this.handleChangeSlot);
        $('.meals-list .meal .participation-checkbox').on('change', MealIndexView.handleParticipationUpdate);
        $('.meals-list .meal .dish-combination.edit').on('click', this.handleCombinedMealEdit.bind(this));
        // $('.meals-list .meal .description .edit').on('click', this.handleCombinedMealEdit.bind(this));
    }

    private handleChangeSlot(event: JQuery.TriggeredEvent) {
        const $slotSelector = $(event.target);
        const $mealContainer = $slotSelector.closest('.meal');
        const mealIsBooked = $mealContainer.find('input[type="checkbox"]').is(':checked');

        if (mealIsBooked) {
            const $mealDate = $mealContainer.data('date');
            const slot = $slotSelector.val();
            $.ajax({
                method: 'POST',
                url: '/menu/meal/'+$mealDate+'/update-slot',
                data: { 'slot': slot },
                dataType: 'json',
                success: function () {
                    // hide default option to auto-select slot [TP##250006]
                    $slotSelector.find('option[value=""]').hide()
                },
                error: function () {
                    alert('An unknown error occurred');
                }
            });
        }
    }

    private static handleParticipationUpdate(event: JQuery.TriggeredEvent) {
        const $updatedDishCheckbox = $(event.target);
        const $mealContainer = $updatedDishCheckbox.closest('.meal');
        let $slotSelector = $mealContainer.find('.slot-selector');

        // do nothing if user is joining a meal
        if ($updatedDishCheckbox.is(':checked')) {
            $slotSelector.find('option[value=""]').hide();
            return;
        }

        const bookedMealCount = $mealContainer.find('input.participation-checkbox:checked').length

        // reset slot selector if user cancelled all booked meals
        if (1 > bookedMealCount) {
            $slotSelector.find('option[value=""]').show();
            $slotSelector.val('');
        }
    }

    private handleCombinedMealEdit(event: JQuery.TriggeredEvent): void {
        const $dishContainer = $(event.target).closest('.meal-row');
        this.showMealConfigurator($dishContainer);
    }

    private updateSlots() {
        $.ajax({
            url: '/participation/slots-status',
            dataType: 'json',
            success: function (data) {
                $.each(data, function (k, v) {
                    const slotSelectorId = 'day-'+v.date.replaceAll('-', '')+'-slots';

                    let $slotSelector = $('#'+slotSelectorId);
                    if ($slotSelector.length < 1) {
                        return;
                    }

                    let $slotOption = $slotSelector.find('option[value="'+v.slot+'"]');

                    const slotLimit = $slotOption.data('limit');
                    if (slotLimit > 0) {
                        const slotTitle = $slotOption.data('title');
                        const slotText = slotTitle + ' (' + v.booked+'/'+slotLimit + ')';
                        $slotOption.text(slotText);
                        // disable slot-option if no. of booked slots reach the slot limit
                        $slotOption.prop('disabled', slotLimit <= v.booked);
                    }

                    if (v.booked_by_user) {
                        // do not overwrite user selected value
                        if ('' === $slotSelector.val()) {
                            $slotOption.prop('selected', true);
                        }
                        $slotSelector.find('option[value=""]').hide();
                        $slotSelector.prop('disabled', false);
                    }

                    if ($slotSelector.hasClass('tmp-disabled') === true) {
                        $slotSelector.removeClass('tmp-disabled').prop('disabled', false)
                            .parent().children('.loader').css('visibility', 'hidden');
                    }
                });
            }
        });
    }

    public showMealConfigurator($dishContainer: JQuery): void {
        let self = this;
        let $mealContainer = $dishContainer.closest('.meal');
        const url = $dishContainer.data('joinUrl');
        const slotSlug: string = $mealContainer.find('.slot-selector').val().toString();
        const title = $dishContainer.find('.title').text();
        const dishes = this.getCombinedMealDishes($mealContainer);
        const $bookedDishIDs = $dishContainer.data('bookedDishes').split(',').map((id: string) => id.trim());
        let cmd = new CombinedMealDialog(
            title,
            dishes,
            $bookedDishIDs,
            slotSlug,
            {
                ok: function (data: unknown) {
                    console.log(data);
                    let $participationCheckbox = $dishContainer.find('input[type=checkbox]');
                    let req = new ParticipationRequest(url, data);
                    ParticipationRequestHandler.sendRequest(req, $participationCheckbox, function(participationCheckbox, data: JoinResponse) {
                        const bookedDishIDs = data.bookedDishes.split(',').map(dishID => dishID.trim());
                        if (0 < bookedDishIDs.length) {
                            self.updateCombinedDishDesc(participationCheckbox, dishes, bookedDishIDs);
                        }
                    });
                }.bind(self)
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

    private updateCombinedDishDesc($dishCheckbox: JQuery, $dishes: Dish[], $selectedDishIDs: string []) {
        let sdt = this.getSelectedDishTitles($selectedDishIDs, $dishes);
        if (0 < sdt.length) {
            $dishCheckbox.closest('.meal-row').find('.description .dish-combination').text(sdt.join(', '));
        }
    }

    private getSelectedDishTitles($selectedDishIDs: string[], dishes: Dish[]|DishVariation[]) {
        let self = this;
        let dishTitles: string[] = [];
        dishes.forEach(function(dish){
            let idx = $selectedDishIDs.indexOf(dish.slug);
            if (-1 < idx) {
                dishTitles.push(dish.title);
                $selectedDishIDs.slice(idx, 1);
            } else if (Array.isArray(dish.variations) && 0 < dish.variations.length) {
                let dvt = self.getSelectedDishTitles($selectedDishIDs, dish.variations);
                dishTitles.push(...dvt);
            }
        });

        return dishTitles;
    }
}

interface JoinResponse {
    participantsCount: number
    url: string
    actionText: string
    bookedDishes: string    // comma separated slugs of selected dishes in combined meal
}
