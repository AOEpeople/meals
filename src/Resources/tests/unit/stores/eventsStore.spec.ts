import useApi from '@/api/api';
import { ref } from 'vue';
import Events from '../fixtures/getEvents.json';
import { useEvents } from '@/stores/eventsStore';

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/events/.test(url) && method === 'GET') {
        return {
            response: ref(Events),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (/api\/events/.test(url) && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('/api/events') && method === 'PUT') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('/api/events') && method === 'DELETE') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        }
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test EventsStore', () => {
    const { EventsState, fetchEvents } = useEvents();

    it('should not contain data before fetching', () => {
        expect(EventsState.events).toEqual([]);
        expect(EventsState.error).toEqual('');
        expect(EventsState.isLoading).toBeFalsy();
    });

    it('should contain data after fetching', async () => {
        await fetchEvents();

        expect(EventsState.events).toEqual(Events);
        expect(EventsState.error).toEqual('');
        expect(EventsState.isLoading).toBeFalsy();
    });
});