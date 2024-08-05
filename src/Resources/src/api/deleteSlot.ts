import { type IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Performs a DELETE request to delete a slot with a given identifier
 * @param slug The identifier of the slot
 */
export default async function deleteSlot(slug: string) {
    const { error, request, response } = useApi<IMessage | null>('DELETE', `api/slots/${slug}`, 'application/json');

    await request();

    return { error, response };
}
