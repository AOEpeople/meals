import { type Dish } from '@/stores/dishesStore';
import useApi from './api';

/**
 * Fetches the dishes a combi meal consists of
 * @param mealId The id of the combi-meal
 */
export default async function getDishesForCombi(mealId: number) {
    const { error, response, request } = useApi<Dish[]>('GET', `api/participations/combi/${mealId}`);

    await request();

    return { error, response };
}
