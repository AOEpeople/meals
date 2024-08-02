import deleteCategory from '@/api/deleteCategory';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';

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

describe('Test deleteCategory', () => {
    it('should return null', async () => {
        const { error, response } = await deleteCategory('others');

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
