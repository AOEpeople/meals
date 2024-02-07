import getDishCount from '@/api/getDishCount';
import { ref } from 'vue';
import DishesCount from '../fixtures/dishesCount.json';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(DishesCount),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getDishCount', () => {
    it('should return a map of ids from dishes with their respective counts', async () => {
        const { response, error } = await getDishCount();

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(DishesCount);
    });
});
