import getDishes from '@/api/getDishes';
import Dishes from '../fixtures/getDishes.json';
import useApi from '@/api/api';
import { ref } from 'vue';
import { describe, it, expect } from '@jest/globals';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Dishes),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getDishes', () => {
    it('should return a list of dishes', async () => {
        const { dishes, error } = await getDishes();

        expect(error.value).toBeFalsy();
        expect(dishes.value).toEqual(Dishes);
    });
});
