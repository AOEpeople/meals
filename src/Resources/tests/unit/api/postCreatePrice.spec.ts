import { ref } from 'vue';
import postCreatePrice from '@/api/postCreatePrice';
import useApi from '@/api/api';
import { vi, describe, it, expect } from 'vitest';
import {PriceCreateData} from "../../../src/api/postCreatePrice";
import Price from '../fixtures/createPriceResponse.json';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Price),
    request: asyncFunc,
    error: ref(false)
};
vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test postCreatePrice', () => {
    it('should create new price and return its data', async () => {
        const pricesCreateData: PriceCreateData = {
            year: 2025,
            price: 4.4,
            price_combined: 6.4
        }
        const { error, response } = await postCreatePrice(pricesCreateData);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Price);
    });
});
