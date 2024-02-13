import getWeeks from '@/api/getWeeks';
import { ref } from 'vue';
import Weeks from '../fixtures/getWeeks.json';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Weeks),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getWeeks', () => {
    it('should return a list of Weeks', async () => {
        const { weeks, error } = await getWeeks();

        expect(error.value).toBeFalsy();
        expect(weeks.value).toEqual(Weeks);
    });
});
