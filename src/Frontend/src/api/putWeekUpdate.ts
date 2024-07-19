import useApi from '@/api/api';
import { WeekDTO } from '@/interfaces/DayDTO';
import { IMessage } from '@/interfaces/IMessage';

export default async function putWeekUpdate(week: WeekDTO) {
    const { error, request, response } = useApi<IMessage>(
        'PUT',
        `api/menu/${week.id}`,
        'application/json',
        JSON.stringify(week)
    );

    await request();

    return { error, response };
}
