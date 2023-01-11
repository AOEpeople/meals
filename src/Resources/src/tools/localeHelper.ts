import type {DateTime} from '@/api/getDashboardData'
import {WritableComputedRef} from 'vue'

export function translateWeekday(date: DateTime, locale: WritableComputedRef<string>): string {
    const parsedDate = new Date(Date.parse(date.date));

    return parsedDate.toLocaleDateString(locale.value, { weekday: 'long' })
}