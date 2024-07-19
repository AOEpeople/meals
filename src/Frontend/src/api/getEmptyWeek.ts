import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';
import { Week } from '@/stores/weeksStore';

/**
 * Fetches an empty week from the backend. Only contains basic information about the week.
 * @param year          The year of the week
 * @param calendarWeek  The iso calendar week
 */
export default async function getEmptyWeek(year: number, calendarWeek: number) {
    const { error, request, response } = useApi<IMessage | Week>(
        'GET',
        `api/weeks/${year}W${calendarWeek.toLocaleString('en-US', { minimumIntegerDigits: 2, useGrouping: false })}`
    );

    await request();

    return { error, response };
}
