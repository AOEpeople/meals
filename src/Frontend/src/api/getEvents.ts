import useApi from './api';
import { type Event } from '@/stores/eventsStore';

/**
 * Performs a GET request and returns an object containing a list of all events
 * and a value indicating if an error occured
 */
export default async function getEvents() {
    const { error, request, response: events } = useApi<Event[]>('GET', 'api/events');

    await request();

    return { error, events };
}
