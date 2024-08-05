import postCreateDishVariation from '@/api/postCreateDishVariation';
import { CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import { ref } from 'vue';
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

const dishVariation: CreateDishVariationDTO = {
    titleDe: 'TestVarDe',
    titleEn: 'TestVarEn'
};

describe('Test postCreateDishVariation', () => {
    it('should return null', async () => {
        const { error, response } = await postCreateDishVariation(dishVariation, 'testen');

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
