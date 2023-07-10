import postCreateDish from "@/api/postCreateDish";
import useApi from "@/api/api";
import success from "../fixtures/Success.json";
import { CreateDishDTO } from "@/api/postCreateDish";
import { it, describe, expect } from "@jest/globals";
import { ref } from "vue";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(success),
    request: asyncFunc,
    error: ref(false)
}

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

const dish: CreateDishDTO = {
    titleDe: 'TestDe',
    titleEn: 'TestEn',
    oneServingSize: false
};

describe('Test postCreateDish', () => {
    it('should return a success object', async () => {
        const { error, response } = await postCreateDish(dish);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(success);
    });
});