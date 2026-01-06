import Update from '../fixtures/priceUpdateResponse.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';
import putUpdatePrice, {PriceUpdateData} from '../../../src/api/putUpdatePrice';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Update.put),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test putPriceUpdate', () => {
    it('should return an updated price', async () => {
        const priceUpdateData: PriceUpdateData = {
            year: 2025,
            price: 4.4,
            price_combined: 6.4
        }
        const { response, error } = await putUpdatePrice(priceUpdateData);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Update.put);
    });
});
