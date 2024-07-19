import useApi from '@/api/api';
import { IMessage } from '@/interfaces/IMessage';
import { Dictionary } from 'types/types';

export default async function getDishCount() {
    const { error, request, response } = useApi<Dictionary<number> | IMessage>('GET', `api/meals/count`);

    await request();

    return { error, response };
}
