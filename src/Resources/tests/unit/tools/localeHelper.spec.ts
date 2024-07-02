import { DateTime } from '@/api/getDashboardData';
import { translateWeekday, translateWeekdayWithoutRef, translateMonth } from '@/tools/localeHelper';
import { computed, ref } from 'vue';

const dateTime: DateTime = {
    date: '2023-07-03 12:00:00.000000',
    timezone_type: 0,
    timezone: ''
};
const localeRef = ref('en');

const computedLocale = computed({
    get() {
        return localeRef.value;
    },
    set(newValue) {
        localeRef.value = newValue;
    }
});

describe('Test localeHelper', () => {
    beforeAll(() => {
        jest.useFakeTimers();
        jest.setSystemTime(new Date(2023, 3, 15));
    });

    afterAll(() => {
        jest.useRealTimers();
    });

    it('should return the correct weekday representation', () => {
        expect(translateWeekday(dateTime, computedLocale)).toBe('Monday');
    });

    it('should return the correct weekday representation without ref', () => {
        expect(translateWeekdayWithoutRef(dateTime, 'en')).toBe('Mon');
        expect(translateWeekdayWithoutRef(dateTime, 'de')).toBe('Mo');
    });

    it('should return the correct month representation', () => {
        expect(translateMonth(dateTime, 'en')).toBe('July');
        expect(translateMonth(dateTime, 'de')).toBe('Juli');

        dateTime.date = new Date().toISOString().split('T')[0];
        expect(translateMonth(dateTime, 'en')).toBe(
            new Date().toLocaleString('en', { month: '2-digit', day: '2-digit' })
        );
    });
});
