import putDishVariationUpdate from '@/api/putDishVariationUpdate';
import { CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import { ref } from 'vue';
import Dishes from '../fixtures/getDishes.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Dishes[0].variations[0]),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

const dish: CreateDishVariationDTO = {
    titleDe: 'TestVarDE',
    titleEn: 'TestVarEN'
};

describe('Test putDishVariationUpdate', () => {
    it('should return a dish object', async () => {
        const { error, response } = await putDishVariationUpdate('testvaren', dish);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Dishes[0].variations[0]);
    });
});
