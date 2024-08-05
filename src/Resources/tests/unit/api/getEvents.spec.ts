import getEvents from '@/api/getEvents';
import Events from '../fixtures/getEvents.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Events),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getEvents', () => {
    it('should return a list of events', async () => {
        const { error, events } = await getEvents();

        expect(error.value).toBeFalsy();
        expect(events.value).toEqual(Events);
    });
});
