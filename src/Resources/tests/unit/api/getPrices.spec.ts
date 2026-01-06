import getPrice from '@/api/getPrices';
import Prices from '../fixtures/getPricesResponse.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Prices),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getPrices', () => {
    it('should return a list of prices', async () => {
        const { error, prices } = await getPrice();

        expect(error.value).toBe(false);
        expect(prices.value).toEqual(Prices);
    });
});
