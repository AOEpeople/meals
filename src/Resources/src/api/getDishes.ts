import { Dish } from '@/stores/dishesStore';
import useApi from './api';

/**
 * Performs a GET request for a list of available dishes
 */
export default async function getDishes() {
    const { error, response: dishes, request } = useApi<Dish[]>('GET', 'api/dishes');

    await request();

    return { error, dishes };
}
