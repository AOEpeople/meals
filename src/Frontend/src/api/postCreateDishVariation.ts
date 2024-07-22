import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';

export interface CreateDishVariationDTO {
    titleDe?: string;
    titleEn?: string;
}

/**
 * Performs a POST to create a new dish variation
 * @param dishVariation The dish variation to create
 * @param parentSlug The identifier of the parent dish
 */
export default async function postCreateDishVariation(dishVariation: CreateDishVariationDTO, parentSlug: string) {
    const { error, response, request } = useApi<IMessage>(
        'POST',
        `api/dishes/${parentSlug}/variation`,
        'application/json',
        JSON.stringify(dishVariation)
    );

    await request();

    return { error, response };
}
