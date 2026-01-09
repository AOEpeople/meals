import { type IPrices } from '@/stores/pricesStore';
import useApi from './api';

export default async function getPrices() {
    const { error, response: prices, request } = useApi<IPrices>('GET', 'api/prices');

    await request();

    if (Array.isArray(prices.value?.prices)) {
        prices.value.prices = {};
    }

    return { error, prices };
}
