import putDishUpdate from '@/api/putDishUpdate';
import { CreateDishDTO } from '@/api/postCreateDish';
import { ref } from 'vue';
import Dishes from '../fixtures/getDishes.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Dishes[0]),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

const dish: CreateDishDTO = {
    titleDe: 'TestDE',
    titleEn: 'TestEN',
    oneServingSize: true
};

describe('Test putDishUpdate', () => {
    it('should return a dish object', async () => {
        const { error, response } = await putDishUpdate('testen', dish);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Dishes[0]);
    });
});
