import putDishVariationUpdate from '@/api/putDishVariationUpdate';
import useApi from '@/api/api';
import { CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import { describe, it, expect } from '@jest/globals';
import { ref } from 'vue';
import Dishes from '../fixtures/getDishes.json';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Dishes[0].variations[0]),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

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
