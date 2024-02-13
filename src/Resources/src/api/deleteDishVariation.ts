import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Performs a DELETE request to delete a dish variation with a given identifier
 * @param slug The identifier of the dish variation
 */
export default async function deleteDishVariation(slug: string) {
    const { error, request, response } = useApi<IMessage | null>(
        'DELETE',
        `api/dishes/variation/${slug}`,
        'application/json'
    );

    await request();

    return { error, response };
}
