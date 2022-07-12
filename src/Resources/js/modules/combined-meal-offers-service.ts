import 'jquery-ui/ui/widgets/dialog';
import {CombinedMealOffersDialog, Offer} from './combined-meal-offers-dialog';
import {AbstractParticipationToggleHandler} from './participation-toggle-handler';
import {ParticipantCounter} from './participant-counter';

export class CombinedMealOffersService {
    public static execute($checkbox: JQuery, participationToggleHandler: AbstractParticipationToggleHandler) {
        this.getOffers($checkbox, participationToggleHandler);
    }

    private static getOffers($checkbox: JQuery, participationToggleHandler: AbstractParticipationToggleHandler) {
        let self = this
        let participantCounter: ParticipantCounter = $checkbox.data(ParticipantCounter.NAME);
        let date = participantCounter.getDay();
        let dish = participantCounter.getDishSlug();
        $.getJSON('/menu/' + date + '/' + dish + '/offers')
            .done(function (offers: Offers) {
                self.openDialog(offers.title, $checkbox, offers.offers, participationToggleHandler);
            });
    }

    private static openDialog(title: string, $checkbox: JQuery, offers: Array<Offer>, participationToggleHandler: AbstractParticipationToggleHandler) {
        let cmd = new CombinedMealOffersDialog(
            title,
            offers,
            {
                ok: function (data) {
                    participationToggleHandler.toggle($checkbox, data);
                }
            }
        );
        cmd.open();
    }
}

interface Offers {
    title: string,
    offers: Array<Offer>
}
