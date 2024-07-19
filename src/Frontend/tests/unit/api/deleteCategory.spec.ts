import deleteCategory from '@/api/deleteCategory';
import useApi from '@/api/api';
import { ref } from 'vue';
import { it, describe, expect } from '@jest/globals';

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

describe('Test deleteCategory', () => {
    it('should return null', async () => {
        const { error, response } = await deleteCategory('others');

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
