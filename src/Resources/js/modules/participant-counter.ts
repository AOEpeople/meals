export enum ParticipationState {
    DEFAULT = 'default',
    PENDING = 'participation-pending',
    OFFER_AVAILABLE = 'offer-available'
}

export class ParticipantCounter {
    public static readonly NAME = 'participant-counter';
    private readonly delimiter = ' / ';

    private readonly mealId: number;
    private readonly dishSlug: string;
    private readonly day: string;
    private readonly dayEnabled: boolean;
    private readonly lockDateTime: Date;

    private offset: number = 0;
    private limit: number;
    private nextCount: number;
    private nextState: ParticipationState;

    private $count: JQuery;
    private $limit: JQuery;
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

        this.$count = this.$participantsCountWrapper.find('span:first-child');
        this.$limit = this.$participantsCountWrapper.find('label');

        this.limit = parseFloat(this.$limit.text().replace(this.delimiter, '').trim()) || 0;
        /*
         * We round float from backend, because we use limits only for usual meals no combined meals and
         * we check if the limit is reached for usual meals.
         */
        if (this.hasLimit()) {
            this.$limit.text(this.delimiter + Math.floor(this.limit));
        }

        this.nextCount = this.getCount();
        this.nextState = this.getParticipationState();
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

    getCount(): number {
        return (parseInt(this.$count.text().trim()) || 0) - this.offset;
    }

    setNextCount(count: number) {
        this.nextCount = count - this.offset;
    }

    getLimit(): number {
        return this.limit;
    }

    setNextLimit(limit: number) {
        this.limit = limit;
    }

    hasLimit(): boolean {
        return 0 < this.getLimit();
    }

    isLimitReached(): boolean {
        return Math.floor(this.limit) <= this.nextCount;
    }

    isAvailable(): boolean {
        return this.dayEnabled && this.lockDateTime.getTime() > Date.now();
    }

    getOffset(): number {
        return this.offset;
    }

    hasOffset(): boolean {
        return 1 === this.offset;
    }

    toggleOffset() {
        this.offset = this.hasOffset() ? 0 : 1;
    }

    getParticipationState(): ParticipationState {
        let classList = this.$participantsCountWrapper.attr('class').split(/\s+/);
        for (let key in classList) {
            if (classList[key] === ParticipationState.PENDING) {
                return ParticipationState.PENDING;
            } else if (classList[key] === ParticipationState.OFFER_AVAILABLE) {
                return ParticipationState.OFFER_AVAILABLE;
            }
        }

        return ParticipationState.DEFAULT;
    }

    setNextParticipationState(state: ParticipationState) {
        this.nextState = state;
    }

    updateUI() {
        this.$count.text(this.nextCount + this.offset);

        let isAvailable = this.isAvailable();
        this.$participantsCountWrapper.toggleClass('participation-allowed', isAvailable);

        if (this.hasLimit()) {
            this.$limit.text(this.delimiter + Math.floor(this.limit));
            let limitIsReached = this.isLimitReached();
            if (limitIsReached && this.$participantsCountWrapper.hasClass('participation-allowed')) {
                this.$participantsCountWrapper.removeClass('participation-allowed')
            }

            this.$participantsCountWrapper.toggleClass('participation-allowed', isAvailable && !limitIsReached);
        }

        let oldState = this.getParticipationState();
        if (oldState !== this.nextState) {
            if (ParticipationState.DEFAULT !== oldState)
                this.$participantsCountWrapper.removeClass(oldState);
            if (ParticipationState.DEFAULT !== this.nextState)
                this.$participantsCountWrapper.addClass(this.nextState);
        }
    }

    toggle(state: boolean) {
        this.$participantsCountWrapper.toggleClass('participation-allowed', state);
    }
}
