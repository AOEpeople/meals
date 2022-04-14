import {ParticipationGuestToggleHandler} from "../modules/participation-toggle-handler";
import {ParticipationPreToggleHandler} from "../modules/participation-pre-toggle-handler";
import {ParticipationUpdateHandler} from "../modules/participation-update-handler";
import {MercureSubscriber} from "../modules/subscriber/mercure-subscriber";
import {SlotAllocationUpdateHandler} from "../modules/slot-allocation-update-handler";

export default class MealGuestView {
    $participationCheckboxes: JQuery;

    constructor() {
        this.$participationCheckboxes = $('.meal-guest input[type="checkbox"]');

        if (this.$participationCheckboxes.length > 0) {
            let participationToggleHandler = new ParticipationGuestToggleHandler(this.$participationCheckboxes);
            new ParticipationPreToggleHandler(participationToggleHandler);
        }

        let messageSubscriber = new MercureSubscriber($('[data-msg-subscribe-url]').data('msgSubscribeUrl'));
        messageSubscriber.subscribe(['participation-updates'], ParticipationUpdateHandler.updateParticipation);
        messageSubscriber.subscribe(['slot-allocation-updates'], SlotAllocationUpdateHandler.handleUpdate);
    }
};
