import getCategoriesData from '@/api/getCategoriesData';
import { ref } from 'vue';
import Categories from '../fixtures/getCategories.json';
import { describe, expect, it, vi } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Categories),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getCategoriesData', () => {
    it('should return a list of categories', async () => {
        const { categories, error } = await getCategoriesData();

        expect(error.value).toBeFalsy();
        expect(categories.value).toEqual(Categories);
    });
});
