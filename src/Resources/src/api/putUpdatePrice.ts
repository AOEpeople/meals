import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

interface PriceUpdateData {
    year: number;
    price: number;
    price_combined: number;
}

export default async function putUpdatePrice(price: PriceUpdateData) {
    const { error, request, response } = useApi<IMessage | null>(
        'PUT',
        `api/price/${price.year}`,
        'application/json',
        JSON.stringify(price)
    );

    await request();

    return { error, response };
}
