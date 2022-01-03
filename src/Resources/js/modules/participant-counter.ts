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

    private state: ParticipationState;

    private counter: number;
    private limit: number;

    private $counter: JQuery;
    private $limit: JQuery;
    private $participantsCountWrapper: JQuery;

    constructor($checkbox: JQuery) {
        this.$participantsCountWrapper = $checkbox.closest(this.parentWrapperClass).find(this.wrapperClass);

        this.$counter = this.$participantsCountWrapper.find(this.counterSelector);
        this.counter = parseInt(this.$counter.text().trim()) || 0;

        this.$limit = this.$participantsCountWrapper.find(this.limitSelector);
        this.limit = parseInt(this.$limit.text().replace(this.delimiter, '').trim()) || 0;

        this.initState();
    }

    getCounter(): number {
        return this.counter;
    }

    setCounter(counter: number) {
        this.counter = counter;
    }

    setLimit(limit: number) {
        this.limit = limit;
    }

    hasLimit() {
        return 0 !== this.limit;
    }

    setParticipationState(participationState: ParticipationState) {
        this.state = participationState;
    }

    updateUI() {
        let self = this;
        this.$participantsCountWrapper.fadeOut('fast', function () {
            self.$counter.text(self.counter);

            if (ParticipationState.DEFAULT !== self.state) {
                self.$participantsCountWrapper.toggleClass(self.state);
            }

            if (self.hasLimit()) {
                self.$limit.text(' ' + self.delimiter + ' ' + self.limit);
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
