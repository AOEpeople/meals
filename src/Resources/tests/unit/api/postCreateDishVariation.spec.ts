import postCreateDishVariation from '@/api/postCreateDishVariation';
import { CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import useApi from '@/api/api';
import { it, describe, expect } from '@jest/globals';
import { ref } from 'vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(null),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

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
