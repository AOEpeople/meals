import useApi from '@/api/api';
import postCreateEvent from '@/api/postCreateEvent';
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

describe('Test postCreateEvent', () => {
    it('should call useApi and return null', async () => {
        const { error, response } = await postCreateEvent('test', true);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
