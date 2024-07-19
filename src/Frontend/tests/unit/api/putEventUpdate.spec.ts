import useApi from '@/api/api';
import putEventUpdate from '@/api/putEventUpdate';
import { Event } from '@/stores/eventsStore';
import { ref } from 'vue';

const testEvent: Event = {
    id: 0,
    title: 'Test',
    slug: 'test',
    public: false
};

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(testEvent),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test putEventUpdate', () => {
    it('should call useApi and return an event', async () => {
        const { error, response } = await putEventUpdate('test', 'Test', true);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(testEvent);
    });
});
