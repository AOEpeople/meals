import getCategoriesData from '@/api/getCategoriesData';
import { ref } from 'vue';
import Categories from '../fixtures/getCategories.json';
import { describe, expect, it } from '@jest/globals';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Categories),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getCategoriesData', () => {
    it('should return a list of categories', async () => {
        const { categories, error } = await getCategoriesData();

        expect(error.value).toBeFalsy();
        expect(categories.value).toEqual(Categories);
    });
});
