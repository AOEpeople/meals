import putEventUpdate from '@/api/putEventUpdate';
import { Event } from '@/stores/eventsStore';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';
import useApi from '@/api/api';

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

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test putEventUpdate', () => {
    it('should call useApi and return an event', async () => {
        const { error, response } = await putEventUpdate('test', 'Test', true);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(testEvent);
    });
});
