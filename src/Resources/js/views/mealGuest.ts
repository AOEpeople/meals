import {ParticipationGuestToggleHandler} from "../modules/participation-toggle-handler";
import {ParticipationPreToggleHandler} from "../modules/participation-pre-toggle-handler";
import {ParticipationUpdateHandler} from "../modules/participation-update-handler";
import {SlotAllocationUpdateHandler} from "../modules/slot-allocation-update-handler";

export default class MealGuestView {
    $participationCheckboxes: JQuery;

    constructor() {
        this.$participationCheckboxes = $('.meal-guest input[type="checkbox"]');
        if (1 > this.$participationCheckboxes.length) {
            return;
        }

        let participationToggleHandler = new ParticipationGuestToggleHandler(this.$participationCheckboxes);
        new ParticipationPreToggleHandler(participationToggleHandler);

        this.configureMealUpdateHandlers();
    }

    /**
     * Configure handlers to process meal push notifications.
     */
    private configureMealUpdateHandlers(): void {
        const event = new EventSource($('.weeks').data('msgSubscribeUrl'), { withCredentials: true });
        event.addEventListener('participationUpdate', (event: MessageEvent) => {
            ParticipationUpdateHandler.updateParticipation(JSON.parse(event.data));
        });
        event.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            SlotAllocationUpdateHandler.handleUpdate(JSON.parse(event.data));
        });
    }
};
