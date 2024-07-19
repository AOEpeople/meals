import { ref } from 'vue';
import deleteSlot from '@/api/deleteSlot';
import { describe, expect, it } from '@jest/globals';
import useApi from '@/api/api';

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

describe('Test postDeleteSlot', () => {
    it('should return null on deleting a slot', async () => {
        const { error, response } = await deleteSlot('1');

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
