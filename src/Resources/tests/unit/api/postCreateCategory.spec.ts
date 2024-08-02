import postCreateCategory from '@/api/postCreateCategory';
import { ref } from 'vue';
import { Category } from '@/stores/categoriesStore';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(null),
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

describe('Test postCreateCategory', () => {
    it('should return null', async () => {
        const { error, response } = await postCreateCategory(category);

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
