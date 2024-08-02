import getDishCount from '@/api/getDishCount';
import { ref } from 'vue';
import DishesCount from '../fixtures/dishesCount.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(DishesCount),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getDishCount', () => {
    it('should return a map of ids from dishes with their respective counts', async () => {
        const { response, error } = await getDishCount();

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(DishesCount);
    });
});
