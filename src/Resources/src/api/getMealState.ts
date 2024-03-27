import useApi from './api';

/**
 * Gets the mealState for a specified Meal
 */
export default async function getMealState(mealId: number) {
    const {
        error,
        response: mealstate,
        request
    } = useApi<string>('GET', `api/mealstate/${mealId}`);

    await request();

    return { error, mealstate };
}
