import getCosts from '@/api/getCosts';
import Costs from '../fixtures/getCosts.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Costs),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getCosts', () => {
    it('should return a list of profiles with costs and dateranges corresponding to the costs', async () => {
        const { error, costs } = await getCosts();

        expect(error.value).toBe(false);
        expect(costs.value).toEqual(Costs);
    });
});
