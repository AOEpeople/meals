import { type ICosts } from '@/stores/costsStore';
import useApi from './api';

/**
 * Performs a GET request for a list of costs.
 */
export default async function getCosts() {
    const { error, response: costs, request } = useApi<ICosts>('GET', 'api/costs');

    await request();

    if (Array.isArray(costs.value?.users)) {
        costs.value.users = {};
    }

    return { error, costs };
}
