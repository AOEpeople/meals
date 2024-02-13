import { IMessage } from '@/interfaces/IMessage';
import useApi from './api';

/**
 * Makes a POST-Request to create an Event
 * @param title     The displayed title of the event
 * @param isPublic  Wether the event is open to guests
 */
export default async function postCreateEvent(title: string, isPublic: boolean) {
    const { error, request, response } = useApi<IMessage | null>(
        'POST',
        'api/events',
        'application/json',
        JSON.stringify({ title: title, public: isPublic })
    );

    await request();

    return { error, response };
}
