import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Performs a DELETE request to delete a dish with a given identifier
 * @param slug The identifier of the dish
 */
export default async function deleteDish(slug: string) {
    const { error, request, response } = useApi<IMessage | null>('DELETE', `api/dishes/${slug}`, 'application/json');

    await request();

    return { error, response };
}
