import type { Event } from '@/stores/eventsStore';
import useApi from './api';
import type { IMessage } from '@/interfaces/IMessage';

/**
 * Updates an Event with an PUT request
 * @param slug      The slug of the event
 * @param title     The title of the event
 * @param isPublic  Wether the event is public
 */
export default async function putEventUpdate(slug: string, title: string, isPublic: boolean) {
    const { error, request, response } = useApi<IMessage | Event>(
        'PUT',
        `api/events/${slug}`,
        'application/json',
        JSON.stringify({ title: title, public: isPublic })
    );

    await request();

    return { error, response };
}
