import putCategoryUpdate from '@/api/putCategoryUpdate';
import { ref } from 'vue';
import Categories from '../fixtures/getCategories.json';
import { Category } from '@/stores/categoriesStore';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Categories[0]),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

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
