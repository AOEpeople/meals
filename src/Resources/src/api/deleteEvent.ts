import useApi from './api';
import { type IMessage } from '@/interfaces/IMessage';

/**
 * Deletes an event with an DELETE request
 * @param slug  The slug of the event to be deleted
 */
export default async function deleteEvent(slug: string) {
    const { error, request, response } = useApi<IMessage | null>('DELETE', `api/events/${slug}`);

    await request();

    return { error, response };
}
