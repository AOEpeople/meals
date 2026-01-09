import Delete from '../fixtures/priceDeleteResponse.json';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';
import {deletePrice, PriceDeleteData} from '../../../src/api/deletePrice';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Delete),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test deletePrice', () => {
    it('should return successful', async () => {
        const priceDeleteData: PriceDeleteData = {
            year: 2025
        }
        const { error, response } = await deletePrice(priceDeleteData);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Delete);
    });
});
