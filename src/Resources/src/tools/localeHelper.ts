import type { DateTime } from '@/api/getDashboardData';
import { type WritableComputedRef } from 'vue';

export function translateWeekday(date: DateTime, locale: WritableComputedRef<string>): string {
    const parsedDate = new Date(Date.parse(date.date));

    return parsedDate.toLocaleDateString(locale.value, { weekday: 'long' });
}

export function translateWeekdayWithoutRef(date: DateTime, locale: string): string {
    return new Date(date.date).toLocaleDateString(locale, { weekday: 'short' });
}

export function translateMonth(date: DateTime, locale: string): string {
    const parsedDate = new Date(date.date).toLocaleDateString(locale, { month: 'long' });
    if (parsedDate === new Date().toLocaleDateString(locale, { month: 'long' })) {
        return new Date().toLocaleDateString(locale, { month: '2-digit', day: '2-digit' });
    }
    return parsedDate;
}
