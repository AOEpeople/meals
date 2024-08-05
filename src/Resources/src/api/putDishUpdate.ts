import type { CreateDishDTO } from '@/api/postCreateDish';
import type { Dish } from '@/stores/dishesStore';
import useApi from './api';
import type { IMessage } from '@/interfaces/IMessage';

/**
 * Performs a PUT request to update a dish
 * @param slug The identifier of the dish to be changed
 * @param dish The dish as it should look after updating
 * @returns The updated dish
 */
export default async function putDishUpdate(slug: string, dish: CreateDishDTO) {
    const { error, request, response } = useApi<Dish | IMessage>(
        'PUT',
        `api/dishes/${slug}`,
        'application/json',
        JSON.stringify(dish)
    );

    await request();

    return { error, response };
}
