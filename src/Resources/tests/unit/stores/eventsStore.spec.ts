import { ref } from 'vue';
import Events from '../fixtures/getEvents.json';
import { useEvents, Event } from '@/stores/eventsStore';
import { flushPromises } from '@vue/test-utils';
import { vi, describe, beforeEach, afterEach, it, expect } from 'vitest';

const testEvent: Event = {
    id: 7,
    title: 'Test1234',
    slug: 'test1234',
    public: false
};

const userStrings = ['Test, User', 'Another, Testuser', 'abcxyz, User123'];

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/events/.test(url) && method === 'GET') {
        return {
            response: ref(JSON.parse(JSON.stringify(Events))),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/events/.test(url) && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/events') && method === 'PUT') {
        return {
            response: ref(testEvent),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/events') && method === 'DELETE') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/participations/event/') && method === 'GET') {
        return {
            response: ref(userStrings),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test EventsStore', () => {
    const {
        EventsState,
        fetchEvents,
        setFilter,
        filteredEvents,
        updateEvent,
        deleteEventWithSlug,
        getEventBySlug,
        resetState,
        getParticipantsForEvent
    } = useEvents();

    beforeEach(() => {
        resetState();
    });

    afterEach(() => {
        vi.clearAllMocks();
    });

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

    it('should filter the state after setting a filterString', async () => {
        await fetchEvents();

        expect(filteredEvents.value).toHaveLength(2);
        expect(filteredEvents.value[0]).toEqual({
            id: 47,
            title: 'Afterwork',
            slug: 'afterwork',
            public: true
        });

        setFilter('alumni');
        await flushPromises();

        expect(filteredEvents.value).toHaveLength(1);
        expect(filteredEvents.value[0]).toEqual({
            id: 48,
            title: 'Alumni Afterwork',
            slug: 'alumni-afterwork',
            public: true
        });
    });

    it('should update the event with the passed in slug after a successful request', async () => {
        await fetchEvents();
        await updateEvent('afterwork', testEvent.slug, testEvent.public);

        const event = getEventBySlug('test1234');

        expect(event.public).toBe(testEvent.public);
        expect(event.id).toBe(testEvent.id);
        expect(event.slug).toBe(testEvent.slug);
        expect(event.title).toBe(testEvent.title);
    });

    it('should get an event by its slug', async () => {
        await fetchEvents();

        expect(EventsState.events).toHaveLength(2);
        expect(EventsState.events[0]).toEqual(Events[0]);
        expect(Events[0].slug).toBe('afterwork');
        expect(EventsState.events[0].slug).toBe('afterwork');
        const event = getEventBySlug('afterwork');

        expect(event).toBeDefined();
        expect(event.id).toBe(Events[0].id);
        expect(event.public).toBe(Events[0].public);
        expect(event.slug).toBe(Events[0].slug);
        expect(event.title).toBe(Events[0].title);
    });

    it('should delete the event after a successful delete request', async () => {
        await fetchEvents();
        expect(EventsState.events).toHaveLength(2);

        await deleteEventWithSlug('afterwork');
        const event = getEventBySlug('afterwork');

        expect(event).toBeUndefined();
        expect(EventsState.events).toHaveLength(1);
    });

    it('should fetch all the users that participate in an event and return a list of their names', async () => {
        const users = await getParticipantsForEvent('2024-01-24 12:00:00.000000');

        expect(users).toHaveLength(3);
        for (const user of userStrings) {
            expect(users).toContain(user);
        }
    });
});
