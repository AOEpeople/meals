export enum ParticipationState {
    DEFAULT = 'default',
    PENDING = 'participation-pending',
    OFFER_AVAILABLE = 'offer-available'
}

export class ParticipantCounter {
    private readonly parentWrapperClass = '.wrapper-meal-actions';
    private readonly wrapperClass = '.participants-count';
    private readonly counterSelector = 'span:first-child';
    private readonly limitSelector = 'label';
    private readonly delimiter = ' / ';

    private readonly mealId: number;
    private readonly dishSlug: string;
    private readonly date: string;

    private state: ParticipationState;

    private count: number;
    private limit: number;

    private $count: JQuery;
    private $limit: JQuery;
    private $participantsCountWrapper: JQuery;

    constructor($checkbox: JQuery) {
        let $participantsActionsWrapper = $checkbox.closest(this.parentWrapperClass);
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

    getCounter(): number {
        return this.count;
    }

    setCounter(counter: number) {
        this.count = counter;
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
        this.state = participationState;
    }

    updateUI() {
        let self = this;
        this.$participantsCountWrapper.fadeOut('fast', function () {
            self.$count.text(self.count);

            if (ParticipationState.DEFAULT !== self.state) {
                self.$participantsCountWrapper.toggleClass(self.state);
            }

            if (self.hasLimit()) {
                self.$limit.text(self.delimiter + self.limit);
                if (self.limit <= self.count) {
                    self.$participantsCountWrapper.addClass('participation-limit-reached');
                } else {
                    self.$participantsCountWrapper.removeClass('participation-limit-reached');
                }
            }

            self.$participantsCountWrapper.fadeIn('fast');
        });
    }

    private initState() {
        let classList = this.$participantsCountWrapper.attr('class').split(/\s+/);
        this.state = ParticipationState.DEFAULT;
        for (let className in classList) {
            if (className === ParticipationState.PENDING) {
                this.state = ParticipationState.PENDING;
                break;
            } else if (className === ParticipationState.OFFER_AVAILABLE) {
                this.state = ParticipationState.OFFER_AVAILABLE;
                break;
            }
        }
    }
}
