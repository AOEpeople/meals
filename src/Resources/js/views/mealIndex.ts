import {ParticipationPreToggleHandler} from "../modules/participation-pre-toggle-handler";
import {ParticipationToggleHandler} from "../modules/participation-toggle-handler";
import {CombinedMealDialog, SerializedFormData} from "../modules/combined-meal-dialog";
import {ParticipationRequest, ParticipationRequestHandler} from "../modules/participation-request-handler";
import {ParticipationResponse} from "../modules/participation-response-handler";
import {CombinedMealService} from "../modules/combined-meal-service";
import {MercureSubscriber} from "../modules/subscriber/mercure-subscriber";
import {MealOfferUpdate, MealOfferUpdateHandler} from "../modules/meal-offer-update-handler";

export default class MealIndexView {
    participationPreToggleHandler: ParticipationPreToggleHandler;
    $participationCheckboxes: JQuery;

    constructor() {
        this.$participationCheckboxes = $('.meals-list .meal .participation-checkbox');
        this.initEvents();

        if (this.$participationCheckboxes.length > 0) {
            let participationToggleHandler = new ParticipationToggleHandler(this.$participationCheckboxes);
            this.participationPreToggleHandler = new ParticipationPreToggleHandler(participationToggleHandler);

        }

        let messageSubscriber = new MercureSubscriber($('.weeks').data('msgSubscribeUrl'));
        messageSubscriber.subscribe(['/participant-update'], MealIndexView.handleUpdateParticipation);
        messageSubscriber.subscribe(['meal-offer-updates'], MealIndexView.handleMealOfferUpdate);
        messageSubscriber.subscribe(['slot-allocation-updates'], MealIndexView.handleSlotAllocationUpdate);
    }

    private initEvents(): void {
        // set handler for slot change event
        $('.meals-list .meal .slot-selector').on('change', this.handleChangeSlot);
        this.$participationCheckboxes.on('change', MealIndexView.handleParticipationUpdate);
        $('.meals-list .meal .meal-row').on('click', ' .title.edit', this.handleCombinedMealEdit.bind(this));
    }

    private static handleUpdateParticipation(data: ParticipationCountData) {
        $(`div[data-id=${data.mealId}] .count`).text(data.count);
        if(data.isAvailable) {
            $(`div[data-id=${data.mealId}] .participants-count`)
                .removeClass('participation-limit-reached')
                .addClass('participation-allowed');
        } else {
            $(`div[data-id=${data.mealId}] .participants-count`)
                .removeClass('participation-allowed')
                .addClass('participation-limit-reached');
        }
    }

    private static handleMealOfferUpdate(data: MealOfferUpdate) {
        let $checkbox = $(`[data-id=${data.mealId}] input[type=checkbox]`);
        if (1 > $checkbox.length) {
            console.log(`error: meal not found, mealId: ${data.mealId}, method: MealIndexView.handleMealOfferUpdate`);
            return;
        }

        MealOfferUpdateHandler.handleUpdate($checkbox, data);
    }

    private static handleSlotAllocationUpdate(data: SlotData)
    {
        let slotOption = $(`#day-${data.date}-slots option[value=${data.slotSlug}]`);
        if (1 !== slotOption.length) {
            return;
        }

        const slotLimit = slotOption.data('limit');
        if (1 > slotLimit) {
            return;
        }

        slotOption.text(`${slotOption.data('title')} (${data.slotCount}/${slotLimit})`);
        slotOption.prop('disabled', slotLimit <= data.slotCount);
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
                    let $participationCheckbox = $dishContainer.find('input[type=checkbox]');
                    const participationID = $dishContainer.attr('data-id');
                    const url = '/meal/participation/-/update'.replace('-', participationID);
                    let req = new ParticipationRequest(url, reqPayload);
                    ParticipationRequestHandler.sendRequest(req, $participationCheckbox, function($checkbox, data: UpdateResponse) {
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

interface UpdateResponse extends ParticipationResponse {
    bookedDishSlugs: string[];
}
