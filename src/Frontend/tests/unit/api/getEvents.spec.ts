import getEvents from '@/api/getEvents';
import Events from '../fixtures/getEvents.json';
import useApi from '@/api/api';
import { ref } from 'vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Events),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getEvents', () => {
    it('should return a list of events', async () => {
        const { error, events } = await getEvents();

        expect(error.value).toBeFalsy();
        expect(events.value).toEqual(Events);
    });
});
