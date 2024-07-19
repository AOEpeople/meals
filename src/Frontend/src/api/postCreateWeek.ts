import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';
import { WeekDTO } from '@/interfaces/DayDTO';

/**
 *
 * @param year
 * @param calendarWeek
 * @param week
 */
export default async function postCreateWeek(year: number, calendarWeek: number, week: WeekDTO) {
    const { error, request, response } = useApi<IMessage | number>(
        'POST',
        `api/weeks/${year}W${calendarWeek.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false })}`,
        'application/json',
        JSON.stringify(week)
    );

    await request();

    return { error, response };
}
