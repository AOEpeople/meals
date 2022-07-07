import {ParticipationPreToggleHandler} from "../modules/participation-pre-toggle-handler";
import {ParticipationToggleHandler} from "../modules/participation-toggle-handler";
import {CombinedMealDialog, SerializedFormData} from "../modules/combined-meal-dialog";
import {ParticipationRequest, ParticipationRequestHandler} from "../modules/participation-request-handler";
import {ParticipationResponse} from "../modules/participation-response-handler";
import {CombinedMealService} from "../modules/combined-meal-service";
import {MealOfferUpdate, MealOfferUpdateHandler} from "../modules/meal-offer-update-handler";
import {ParticipationUpdateHandler} from "../modules/participation-update-handler";
import {SlotAllocationUpdateHandler} from "../modules/slot-allocation-update-handler";
import {MealService} from "../modules/meal-service";
import AjaxErrorHandler from "../modules/ajax-error-handler";

interface UpdateResponse extends ParticipationResponse {
    bookedDishSlugs: string[];
}

export default class MealIndexView {
    participationPreToggleHandler: ParticipationPreToggleHandler;
    $participationCheckboxes: JQuery;

    constructor() {
        this.$participationCheckboxes = $('.meals-list .meal .participation-checkbox');
        this.initEvents();
        this.configureMealUpdateHandlers();

        if (this.$participationCheckboxes.length > 0) {
            let participationToggleHandler = new ParticipationToggleHandler(this.$participationCheckboxes);
            this.participationPreToggleHandler = new ParticipationPreToggleHandler(participationToggleHandler);
        }
    }

    /**
     * Configure handlers to process meal push notifications.
     */
    private configureMealUpdateHandlers(): void {
        const event = new EventSource($('.weeks').data('msgSubscribeUrl'), { withCredentials: true });
        event.addEventListener('participationUpdate', (event: MessageEvent) => {
            ParticipationUpdateHandler.updateParticipation(JSON.parse(event.data));
        });
        event.addEventListener('mealOfferUpdate', (event: MessageEvent) => {
            MealIndexView.handleMealOfferUpdate(JSON.parse(event.data));
        });
        event.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            SlotAllocationUpdateHandler.handleUpdate(JSON.parse(event.data));
        });
    }

    private initEvents(): void {
        // set handler for slot change event
        $('.meals-list .meal .slot-selector').on('change', this.handleChangeSlot);
        this.$participationCheckboxes.on('change', MealIndexView.postToggleParticipation);
        $('.meals-list .meal .meal-row').on('click', ' .title.edit', this.handleCombinedMealEdit.bind(this));
    }

    private static handleMealOfferUpdate(data: MealOfferUpdate) {
        let $checkbox = $(`[data-id=${data.mealId}] input[type=checkbox]`);
        if (1 > $checkbox.length) {
            console.log(`error: meal not found, mealId: ${data.mealId}, method: MealIndexView.handleMealOfferUpdate`);
            return;
        }

        MealOfferUpdateHandler.handleUpdate($checkbox, data);
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
                error: function (jqXHR) {
                    AjaxErrorHandler.handleError(jqXHR);
                }
            });
        }
    }

    private static postToggleParticipation(event: JQuery.TriggeredEvent) {
        const $updatedDishCheckbox = $(event.target);
        const $mealContainer = $updatedDishCheckbox.closest('.meal');
        let $slotSelector = $mealContainer.find('.slot-selector');

        // hide default slot option (autoselect) if user joined a meal
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
        let mealTitle = $(event.target);
        let mealContainer = mealTitle.closest('.meal');
        const mealLockDateTime = Date.parse(mealContainer.data('lockDateTime'));
        if (mealLockDateTime <= Date.now()) {
            const errMsg = mealContainer.closest('.weeks').data('errUpdateNotPossible');
            if (errMsg.length > 0) {
                alert(errMsg);
            }
            mealTitle.removeClass('edit');
            return;
        }

        const $dishContainer = mealContainer.find('.meal-row.combined-meal');
        this.showMealConfigurator($dishContainer);
    }

    private showMealConfigurator($dishContainer: JQuery): void {
        let self = this;
        let $mealContainer = $dishContainer.closest('.meal');
        const slotSlug: string = $mealContainer.find('.slot-selector').val().toString();
        const title = $dishContainer.find('.title').text();
        const dishes = CombinedMealService.getDishes($mealContainer);
        const $bookedDishIDs = $dishContainer.attr('data-booked-dishes').split(',').map((id: string) => id.trim());
        let cmd = new CombinedMealDialog(
            title,
            dishes,
            $bookedDishIDs,
            slotSlug,
            {
                ok: function (reqPayload: SerializedFormData) {
                    let $mealCheckbox = $dishContainer.find('input[type=checkbox]');

                    const participationID = MealService.getParticipantId($mealCheckbox);
                    const url = `/meal/participation/${participationID}/update`;
                    let req = new ParticipationRequest(url, reqPayload);
                    ParticipationRequestHandler.sendRequest(req, $mealCheckbox, function($checkbox, data: UpdateResponse) {
                        if (0 < data.bookedDishSlugs.length) {
                            CombinedMealService.updateBookedDishes($checkbox, dishes, data.bookedDishSlugs);
                        }
                    });
                }.bind(self)
            }
        );
        cmd.open();
    }
}
