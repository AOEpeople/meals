import {ParticipantCounter} from "./participant-counter";
import {ParticipationUpdateData, ParticipationUpdateHandler} from "./participation-update-handler";

export abstract class AbstractParticipationCountUpdateHandler {
    //protected readonly updateInterval: number = 5000;

    constructor($checkboxes: JQuery) {
        this.initCallback($checkboxes);
    }

    protected abstract initCallback($checkboxes: JQuery): void;
}

export class ParticipationCountUpdateHandler extends AbstractParticipationCountUpdateHandler {
    protected initCallback($checkboxes: JQuery): void {
        if ($checkboxes.length > 0) {
           //window.setInterval(this.participationCountStatus, this.updateInterval, $checkboxes);
        }
    }

    private participationCountStatus($checkboxes: JQuery) {
        $.ajax({
            dataType: 'json',
            method: 'GET',
            url: '/participation/count-status',
            success: function (data) {
                $checkboxes.each(function (idx, checkbox) {
                    let $checkbox = $(checkbox);
                    let participantCounter = $checkbox.data(ParticipantCounter.NAME);
                    let day = participantCounter.getDay();
                    if (undefined !== data[day]) {
                        let mealId = participantCounter.getMealId();
                        let dishSlug = participantCounter.getDishSlug();
                        if (undefined !== data[day]['countByMealIds'][mealId][dishSlug]) {
                            let $dishContainer = $checkbox.closest('.meal-row');
                            const mealStatus = data[day]['countByMealIds'][mealId];
                            if (undefined !== mealStatus['availableWith']) {
                                $dishContainer.attr('data-available-dishes', mealStatus['availableWith'].join(','));
                            } else {
                                $dishContainer.attr('data-available-dishes', '');
                            }

                            let update = getParticipationUpdateData($checkbox, data[day], mealId, dishSlug);
                            ParticipationUpdateHandler.updateParticipation($checkbox, update);
                        } else {
                            console.log("Warning: Values for count status update undefined. No values on " + day + " for meal " + mealId + " and dish " + dishSlug);
                        }
                    }
                });
            },
            error: function (xhr) {
                console.log(xhr.status + ': ' + xhr.statusText);
            }
        });
    }
}

export class ParticipationGuestCountUpdateHandler extends AbstractParticipationCountUpdateHandler {
    protected initCallback($checkboxes: JQuery): void {
        const date = $('.meal-guest').data('date');
        //window.setInterval(this.participationCountStatusByDate, this.updateInterval, $checkboxes, date);
    }

    private participationCountStatusByDate($checkboxes: JQuery, date: string): void {
        $.ajax({
            dataType: 'json',
            method: 'GET',
            url: '/participation/count-status/' + date,
            success: function (data) {
                $checkboxes.each(function (idx, checkbox) {
                    let $checkbox = $(checkbox);
                    let participantCounter = $checkbox.data(ParticipantCounter.NAME);
                    let mealId = participantCounter.getMealId();
                    let dishSlug = participantCounter.getDishSlug();
                    if (undefined !== data['countByMealIds'][mealId][dishSlug]) {
                        let $dishContainer = $checkbox.closest('.meal-row');
                        const mealStatus = data['countByMealIds'][mealId];
                        if (undefined !== mealStatus['availableWith']) {
                            $dishContainer.attr('data-available-dishes', mealStatus['availableWith'].join(','));
                        } else {
                            $dishContainer.attr('data-available-dishes', '');
                        }

                        let update = getParticipationUpdateData($checkbox, data, mealId, dishSlug);
                        ParticipationUpdateHandler.updateParticipation($checkbox, update);
                    } else {
                        console.log("Warning: Values for count status update undefined. No values for meal " + mealId + " and dish " + dishSlug);
                    }
                });
            },
            error: function (xhr) {
                console.log(xhr.status + ': ' + xhr.statusText);
            }
        });
    }
}

function getParticipationUpdateData($checkbox: JQuery, data: any, mealId: number, dishSlug: string): ParticipationUpdateData {
    let count: number, limit: number;
    let $dishContainer = $checkbox.closest('.meal-row');
    if ($dishContainer.hasClass('combined-meal')) {
        count = data['countByMealIds'][mealId][dishSlug]['count'];
        limit = data['countByMealIds'][mealId][dishSlug]['limit'];
    } else {
        limit = data['totalCountByDishSlugs'][dishSlug]['limit'];
        count = Math.ceil(data['totalCountByDishSlugs'][dishSlug]['count']);
    }

    return {
        mealId: mealId,
        count: count,
        limit: limit,
        available: data['countByMealIds'][mealId]['available'],
        open: data['countByMealIds'][mealId]['open']
    };
}
