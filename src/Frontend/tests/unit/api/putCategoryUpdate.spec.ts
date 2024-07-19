import putCategoryUpdate from '@/api/putCategoryUpdate';
import useApi from '@/api/api';
import { ref } from 'vue';
import Categories from '../fixtures/getCategories.json';
import { it, describe, expect } from '@jest/globals';
import { Category } from '@/stores/categoriesStore';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Categories[0]),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

const category: Category = {
    id: 1,
    titleDe: 'Test',
    titleEn: 'Test',
    slug: 'test'
};

describe('Test putCategoryUpdate', () => {
    it('should return a success object', async () => {
        const { error, response } = await putCategoryUpdate('test', category.titleDe, category.titleEn);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Categories[0]);
    });
});
