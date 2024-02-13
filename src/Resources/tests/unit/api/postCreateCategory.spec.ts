import postCreateCategory from '@/api/postCreateCategory';
import useApi from '@/api/api';
import { ref } from 'vue';
import { it, describe, expect } from '@jest/globals';
import { Category } from '@/stores/categoriesStore';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(null),
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

describe('Test postCreateCategory', () => {
    it('should return null', async () => {
        const { error, response } = await postCreateCategory(category);

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
