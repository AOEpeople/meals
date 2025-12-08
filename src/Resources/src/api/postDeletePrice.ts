import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

interface PriceDeleteData {
    year: number;
}

export default async function postDeletePrice(data: PriceDeleteData) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/prices/delete',
        'application/json',
        JSON.stringify(data)
    );

    await request();

    return { error, response };
}
