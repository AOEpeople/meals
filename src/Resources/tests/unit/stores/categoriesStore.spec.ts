import { Category, useCategories } from '@/stores/categoriesStore';
import Categories from '../fixtures/getCategories.json';
import { ref } from 'vue';
import { vi, describe, beforeEach, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/categories') && method === 'GET') {
        return {
            response: ref(Categories),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/categories') && (method === 'POST' || method === 'DELETE')) {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/categories') && method === 'PUT') {
        return {
            response: ref(category2),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

const category1: Category = {
    id: 4,
    titleDe: 'Sonstiges',
    titleEn: 'Others',
    slug: 'others'
};

const category2: Category = {
    id: 4,
    titleDe: 'Vegetarisch',
    titleEn: 'Vegetarian',
    slug: 'others'
};

describe('Test categoriesStore', () => {
    const { resetState, CategoriesState, fetchCategories, editCategory } = useCategories();

    beforeEach(() => {
        resetState();
    });

    it('should not contain data before fetching', () => {
        expect(CategoriesState.categories).toEqual([]);
        expect(CategoriesState.error).toBe('');
        expect(CategoriesState.isLoading).toBeFalsy();
    });

    it('should contain a list of categories after fetching', async () => {
        await fetchCategories();

        expect(CategoriesState.categories).toEqual(Categories);
        expect(CategoriesState.error).toBe('');
        expect(CategoriesState.isLoading).toBeFalsy();
    });

    it('should update the state', async () => {
        await fetchCategories();

        expect(CategoriesState.categories[0]).toEqual(category1);

        await editCategory(0, 'Vegetarisch', 'Vegetarian');

        expect(CategoriesState.categories[0]).toEqual(category2);
    });
});
