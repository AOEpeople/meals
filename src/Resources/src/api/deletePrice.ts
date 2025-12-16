import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

interface PriceDeleteData {
    year: number;
}

export async function deletePrice(data: PriceDeleteData) {
    const { error, request, response } = useApi<IMessage | null>(
        'DELETE',
        `api/price/${data.year}`,
        'application/json',
        JSON.stringify(data)
    );

    await request();

    return { error, response };
}
