import {ParticipantCounter} from "./participant-counter";

export abstract class AbstractParticipationCountUpdateHandler {
    protected readonly updateInterval: number = 5000;

    constructor($checkboxes: JQuery) {
        this.initCallback($checkboxes);
    }

    protected abstract initCallback($checkboxes: JQuery): void;

    static updateCountStatus(participantCounter: ParticipantCounter, count: number, limit: number): void {
        if (participantCounter.getCounter() !== count || participantCounter.getLimit() !== limit) {
            participantCounter.setCounter(count);
            participantCounter.setLimit(limit);
            participantCounter.updateUI();
        }
    }
}

export class ParticipationCountUpdateHandler extends AbstractParticipationCountUpdateHandler {
    protected initCallback($checkboxes: JQuery): void {
        if ($checkboxes.length > 0) {
            window.setInterval(this.participationCountStatus, this.updateInterval, $checkboxes);
        }
    }

    private participationCountStatus($checkboxes: JQuery) {
        $.ajax({
            url: '/participation/count-status',
            dataType: 'json',
            success: function (data) {
                $checkboxes.each(function (idx, checkbox) {
                    let $checkbox = $(checkbox);
                    let participantCounter = $checkbox.data('participantCounter');
                    let countStatus = data[participantCounter.getDate()]['countByMealIds'][participantCounter.getMealId()][participantCounter.getDishSlug()];
                    if (undefined !== countStatus) {
                        AbstractParticipationCountUpdateHandler.updateCountStatus(participantCounter, countStatus['count'], countStatus['limit']);
                    } else {
                        console.log("Values for count status update undefined. " + participantCounter.getDate() + " " + participantCounter.getMealId() + " " + participantCounter.getDishSlug());
                    }
                });
            }
        });
    }
}

export class ParticipationGuestCountUpdateHandler extends AbstractParticipationCountUpdateHandler {
    protected initCallback($checkboxes: JQuery): void {
        const date = $('.meal-guest').data('date');
        window.setInterval(this.participationCountStatusByDate, this.updateInterval, $checkboxes, date);
    }

    private participationCountStatusByDate($checkboxes: JQuery, date: string): void {
        $.ajax({
            'url': '/participation/count-status/' + date,
            dataType: 'json',
            'success': function (data) {
                $checkboxes.each(function (idx, checkbox) {
                    let $checkbox = $(checkbox);
                    let participantCounter = $checkbox.data('participantCounter');
                    let countStatus = data[participantCounter.getMealId()][participantCounter.getDishSlug()];
                    if (undefined !== countStatus) {
                        // Guests can temporarily reserve a meal via checkbox, but the meal will only be booked after submitting the form
                        let count = countStatus['count'] + ($checkbox.is(':checked') ? 1 : 0);
                        AbstractParticipationCountUpdateHandler.updateCountStatus(participantCounter, count, countStatus['limit']);
                    } else {
                        console.log("Values for count status update undefined.");
                    }
                });
            }
        });
    }
}