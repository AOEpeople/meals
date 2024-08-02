import postCreateDish from '@/api/postCreateDish';
import { CreateDishDTO } from '@/api/postCreateDish';
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

const dish: CreateDishDTO = {
    titleDe: 'TestDe',
    titleEn: 'TestEn',
    oneServingSize: false
};

describe('Test postCreateDish', () => {
    it('should return null', async () => {
        const { error, response } = await postCreateDish(dish);

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
