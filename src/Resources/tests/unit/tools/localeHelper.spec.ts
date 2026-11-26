import { DateTime } from '@/api/getDashboardData';
import { translateWeekday, translateWeekdayWithoutRef, translateMonth } from '@/tools/localeHelper';
import { describe, beforeAll, afterAll, it, expect, vi } from 'vitest';
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
        vi.useFakeTimers();
        vi.setSystemTime(new Date(2023, 3, 15));
    });

    afterAll(() => {
        vi.useRealTimers();
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

        const currentMonth = { ...dateTime, date: '2023-04-01 00:00:00.000000' };
        expect(translateMonth(currentMonth, 'en')).toBe('April');
        expect(translateMonth(currentMonth, 'de')).toBe('April');
    });
});
