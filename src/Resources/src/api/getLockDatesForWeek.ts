import { Dictionary } from 'types/types';
import { DateTime } from './getDashboardData';
import useApi from './api';

export default async function getLockDatesForWeek(weekId: number) {
    const { error, response, request } = useApi<Dictionary<DateTime>>('GET', `api/week/lockdates/${weekId}`);

    await request();

    return { error, response };
}
