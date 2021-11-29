export default function MealIndexView() {
    this.updateSlots();
    setInterval(this.updateSlots, 5000);

    this.initEvents();
};

MealIndexView.prototype.initEvents = function () {
    // set handler for slot change event
    $('.meals-list .meal .slot-selector').change(this.handleChangeSlot);
}

MealIndexView.prototype.handleChangeSlot = function (event) {
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
            error: function () {
                alert('An unknown error occurred');
            }
        });
    }
}

MealIndexView.prototype.updateSlots = function () {
    $.ajax({
        'url': '/participations/slots-status',
        dataType: 'json',
        'success': function (data) {
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
                    if (slotLimit <= v.booked) {
                        $slotOption.prop('disabled', true);
                    } else {
                        $slotOption.prop('disabled', false);
                    }
                }

                if (v.booked_by_user) {
                    // do not overwrite user selected value
                    if ('' === $slotSelector.val()) {
                        $slotOption.prop('selected', true);
                    }
                    $slotSelector.prop('disabled', false);
                }
            });
        }
    });
}
