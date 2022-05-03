export class ParticipantCounter {
    public static readonly NAME = 'participant-counter';

    private readonly mealId: number;
    private readonly dishSlug: string;
    private readonly day: string;
    private readonly dayEnabled: boolean;
    private readonly lockDateTime: Date;

    private $participantsCountWrapper: JQuery;

    constructor($participantsActionsWrapper: JQuery) {
        this.$participantsCountWrapper = $participantsActionsWrapper.find('.participants-count');

        this.mealId = $participantsActionsWrapper.data('id');
        this.dishSlug = $participantsActionsWrapper.closest('.meal-row').data('slug');
        if (undefined === this.dishSlug) {
            this.dishSlug = $participantsActionsWrapper.closest('.variation-row').data('slug');
        }

        let $meal = this.$participantsCountWrapper.closest('.meal');
        this.day = $meal.data('date');
        this.lockDateTime = new Date(Date.parse($meal.data('lock-date-time')));
        this.dayEnabled = $meal.data('day-enabled') === 1;
    }

    getMealId(): number {
        return this.mealId;
    }

    getDishSlug(): string {
        return this.dishSlug;
    }

    getDay(): string {
        return this.day;
    }

    isAvailable(): boolean {
        return this.dayEnabled && this.lockDateTime.getTime() > Date.now();
    }
}
