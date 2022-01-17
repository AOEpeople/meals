import {ParticipationGuestToggleHandler} from "../modules/participation-toggle-handler";
import {ParticipationPreToggleHandler} from "../modules/participation-pre-toggle-handler";
import {ParticipationGuestCountUpdateHandler} from "../modules/participation-count-update-handler";

export default class MealGuestView {
    $participationCheckboxes: JQuery;

    constructor() {
        this.updateSlots();
        setInterval(this.updateSlots, 3000);

        this.$participationCheckboxes = $('.meal-guest input[type="checkbox"]');

        if (this.$participationCheckboxes.length > 0) {
            let participationToggleHandler = new ParticipationGuestToggleHandler(this.$participationCheckboxes);
            new ParticipationPreToggleHandler(participationToggleHandler);
            new ParticipationGuestCountUpdateHandler(this.$participationCheckboxes);
        }
    }

    private updateSlots() {
        let $slotSelector = $('#invitation_form_slot');
        if ($slotSelector.length < 1) {
            return;
        }

        const date = $slotSelector.closest('.meal-guest').data('date');

        $.ajax({
            'url': '/participation/slots-status/' + date,
            dataType: 'json',
            'success': function (data) {
                $.each(data, function (k, v) {
                    let $slotOption = $slotSelector.find('option[value="'+v.slot+'"]');

                    const slotLimit = $slotOption.data('limit');
                    if (slotLimit > 0) {
                        const slotTitle = $slotOption.data('title');
                        const slotText = slotTitle + ' (' + v.booked+'/'+slotLimit + ')';
                        $slotOption.text(slotText);
                        // disable slot if no. of bookings reached the slot limit
                        if (slotLimit <= v.booked) {
                            $slotOption.prop('disabled', true);
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
};
