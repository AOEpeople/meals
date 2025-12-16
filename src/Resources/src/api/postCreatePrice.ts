import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

interface PriceCreateData {
    year: number;
    price: number;
    price_combined: number;
}

export default async function postCreatePrice(price: PriceCreateData) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        `api/price`,
        'application/json',
        JSON.stringify(price)
    );

    await request();

    return { error, response };
}
