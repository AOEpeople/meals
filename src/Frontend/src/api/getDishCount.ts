import useApi from '@/api/api';
import type { IMessage } from '@/interfaces/IMessage';
import type { Dictionary } from '@/types/types';

export default async function getDishCount() {
    const { error, request, response } = useApi<Dictionary<number> | IMessage>('GET', `api/meals/count`);

    await request();

    return { error, response };
}
