import useApi from './api';
import { type Event } from '@/stores/eventsStore';

/**
 * Performs a GET request and returns an object containing a list of all events
 * and a value indicating if an error occured
 */
export default async function getEvents() {
    console.log('Get Events')
    const { error, request, response: events } = useApi<Event[]>('GET', 'api/events');

    await request();
    console.log(JSON.stringify(events))
    return { error, events };
}
