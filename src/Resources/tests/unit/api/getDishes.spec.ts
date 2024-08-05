import getDishes from '@/api/getDishes';
import Dishes from '../fixtures/getDishes.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Dishes),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getDishes', () => {
    it('should return a list of dishes', async () => {
        const { dishes, error } = await getDishes();

        expect(error.value).toBeFalsy();
        expect(dishes.value).toEqual(Dishes);
    });
});
