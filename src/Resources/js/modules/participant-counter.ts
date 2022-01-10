export enum ParticipationState {
    DEFAULT = 'default',
    PENDING = 'participation-pending',
    OFFER_AVAILABLE = 'offer-available'
}

export class ParticipantCounter {
    public static readonly NAME = 'participant-counter';
    public static readonly PARENT_WRAPPER_CLASS = '.wrapper-meal-actions';
    private readonly wrapperClass = '.participants-count';
    private readonly counterSelector = 'span:first-child';
    private readonly limitSelector = 'label';
    private readonly delimiter = ' / ';

    private readonly mealId: number;
    private readonly dishSlug: string;
    private readonly day: string;
    private readonly dayEnabled: boolean;
    private readonly lockDateTime: Date;

    private offset: number = 0;
    private nextCount: number;
    private nextLimit: number;
    private nextState: ParticipationState;

    private $count: JQuery;
    private $limit: JQuery;
    private $participantsCountWrapper: JQuery;

    constructor($participantsActionsWrapper: JQuery) {
        this.$participantsCountWrapper = $participantsActionsWrapper.find(this.wrapperClass);

        this.mealId = $participantsActionsWrapper.data('id');
        this.dishSlug = $participantsActionsWrapper.closest('.meal-row').data('slug');
        if (undefined === this.dishSlug) {
            this.dishSlug = $participantsActionsWrapper.closest('.variation-row').data('slug');
        }

        let $meal = this.$participantsCountWrapper.closest('.meal');
        this.day = $meal.data('date');
        this.lockDateTime = new Date(Date.parse($meal.data('lock-date-time')));
        this.dayEnabled = $meal.data('day-enabled') === 1;

        this.$count = this.$participantsCountWrapper.find(this.counterSelector);
        this.$limit = this.$participantsCountWrapper.find(this.limitSelector);

        this.nextCount = this.getCount();
        this.nextLimit = this.getLimit();
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
        return parseFloat(this.$limit.text().replace(this.delimiter, '').trim()) || 0;
    }

    setNextLimit(limit: number) {
        this.nextLimit = limit;
    }

    hasLimit(): boolean {
        return 0 < this.getLimit();
    }

    isLimitReached(): boolean {
        return this.getLimit() <= this.getCount();
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
        let self = this;
        this.$participantsCountWrapper.fadeOut('fast', function () {
            self.$count.text(self.nextCount + self.offset);

            let isAvailable = self.isAvailable()
            self.$participantsCountWrapper.toggleClass('participation-allowed', isAvailable)

            if (self.hasLimit()) {
                self.$limit.text(self.delimiter + self.nextLimit);
                let limitIsReached = self.isLimitReached();
                self.$participantsCountWrapper.toggleClass('participation-limit-reached', limitIsReached);
                self.$participantsCountWrapper.toggleClass('participation-allowed', isAvailable && !limitIsReached);
            }

            let oldState = self.getParticipationState();
            if (oldState !== self.nextState) {
                if (ParticipationState.DEFAULT !== oldState)
                    self.$participantsCountWrapper.removeClass(oldState);
                if (ParticipationState.DEFAULT !== self.nextState)
                    self.$participantsCountWrapper.addClass(self.nextState);
            }

            self.$participantsCountWrapper.fadeIn('fast');
        });
    }
}
