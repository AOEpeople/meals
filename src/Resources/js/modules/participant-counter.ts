export enum ParticipationState {
    DEFAULT = 'default',
    PENDING = 'participation-pending',
    OFFER_AVAILABLE = 'offer-available'
}

export class ParticipantCounter {
    public static readonly PARENT_WRAPPER_CLASS = '.wrapper-meal-actions';
    private readonly wrapperClass = '.participants-count';
    private readonly counterSelector = 'span:first-child';
    private readonly limitSelector = 'label';
    private readonly delimiter = ' / ';

    private readonly mealId: number;
    private readonly dishSlug: string;
    private readonly date: string;

    private oldState: ParticipationState;
    private currState: ParticipationState;

    private count: number;
    private limit: number;

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
        this.date = this.$participantsCountWrapper.closest('.meal').data('date');

        this.$count = this.$participantsCountWrapper.find(this.counterSelector);
        this.count = parseInt(this.$count.text().trim()) || 0;

        this.$limit = this.$participantsCountWrapper.find(this.limitSelector);
        this.limit = parseFloat(this.$limit.text().replace(this.delimiter, '').trim()) || 0;

        this.initState();
    }

    getMealId(): number {
        return this.mealId;
    }

    getDishSlug(): string {
        return this.dishSlug;
    }

    getDate(): string {
        return this.date;
    }

    getCount(): number {
        return this.count;
    }

    setCount(count: number) {
        this.count = count;
    }

    getLimit(): number {
        return this.limit;
    }

    setLimit(limit: number) {
        this.limit = limit;
    }

    hasLimit() {
        return 0 < this.limit;
    }

    setParticipationState(participationState: ParticipationState) {
        this.oldState = this.currState;
        this.currState = participationState;
    }

    updateUI() {
        let self = this;
        this.$participantsCountWrapper.fadeOut('fast', function () {
            self.$count.text(self.count);

            if (self.oldState !== self.currState) {
                if (ParticipationState.DEFAULT !== self.oldState)
                    self.$participantsCountWrapper.removeClass(self.oldState);
                if (ParticipationState.DEFAULT !== self.currState)
                    self.$participantsCountWrapper.addClass(self.currState);
                self.oldState = self.currState;
            }

            if (self.hasLimit()) {
                self.$limit.text(self.delimiter + self.limit);
                self.$participantsCountWrapper.toggleClass('participation-limit-reached', self.limit <= self.count);
            }

            self.$participantsCountWrapper.fadeIn('fast');
        });
    }

    private initState() {
        let classList = this.$participantsCountWrapper.attr('class').split(/\s+/);
        this.oldState = ParticipationState.DEFAULT;
        this.currState = ParticipationState.DEFAULT;
        for (let key in classList) {
            if (classList[key] === ParticipationState.PENDING) {
                this.currState = ParticipationState.PENDING;
                break;
            } else if (classList[key] === ParticipationState.OFFER_AVAILABLE) {
                this.currState = ParticipationState.OFFER_AVAILABLE;
                break;
            }
        }
    }
}
