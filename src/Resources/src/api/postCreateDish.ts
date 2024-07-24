import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';
import { Diet } from '@/enums/Diet';

export interface CreateDishDTO {
    titleDe: string;
    titleEn: string;
    oneServingSize: boolean;
    descriptionDe?: string;
    descriptionEn?: string;
    category?: number;
    diet?: Diet;
}

/**
 * Performs a POST to create a new dish
 * @param dish The dish to create
 */
export default async function postCreateDish(dish: CreateDishDTO) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/dishes',
        'application/json',
        JSON.stringify(dish)
    );

    await request();

    return { error, response };
}
