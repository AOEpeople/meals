import getCosts from '@/api/getCosts';
import Costs from '../fixtures/getCosts.json';
import useApi from '@/api/api';
import { ref } from 'vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Costs),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getCosts', () => {
    it('should return a list of profiles with costs and dateranges corresponding to the costs', async () => {
        const { error, costs } = await getCosts();

        expect(error.value).toBe(false);
        expect(costs.value).toEqual(Costs);
    });
});
