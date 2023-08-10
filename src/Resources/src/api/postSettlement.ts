import useApi from '@/api/api';
import { IMessage } from '@/interfaces/IMessage';

export default async function postSettlement(username: string) {
    const { error, request, response } = useApi<IMessage | number>(
        'POST',
        `api/costs/settlement/${username}`
    );

    await request();

    return { error, response };
}