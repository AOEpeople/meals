import putDishUpdate from '@/api/putDishUpdate';
import useApi from '@/api/api';
import { CreateDishDTO } from '@/api/postCreateDish';
import { it, describe, expect } from '@jest/globals';
import { ref } from 'vue';
import Dishes from '../fixtures/getDishes.json';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Dishes[0]),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

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
