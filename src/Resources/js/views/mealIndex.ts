export default class MealIndexView {
    constructor() {
        this.updateSlots();
        setInterval(this.updateSlots, 3000);

        this.initEvents();
    }

    private initEvents(): void {
        // set handler for slot change event
        $('.meals-list .meal .slot-selector').on('change', this.handleChangeSlot);
        $('.meals-list .meal .participation-checkbox').on('change', MealIndexView.handleParticipationUpdate);
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
        //
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
}
