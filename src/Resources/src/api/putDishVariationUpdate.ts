import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';
import { CreateDishVariationDTO } from './postCreateDishVariation';
import { Dish } from '@/stores/dishesStore';

/**
 * Performs a PUT request to update a dish variation
 * @param slug The identifier of the dish variation to be changed
 * @param dishVariation The dish variation as it should look after updating
 * @returns The updated dish variation
 */
export default async function putDishVariationUpdate(slug: string, dishVariation: CreateDishVariationDTO) {
    const { error, request, response } = useApi<Dish | IMessage>(
        'PUT',
        `api/dishes/variation/${slug}`,
        'application/json',
        JSON.stringify(dishVariation)
    );

    await request();

    return { error, response };
}
