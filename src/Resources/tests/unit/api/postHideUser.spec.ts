import useApi from '@/api/api';
import postHideUser from '@/api/postHideUser';
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

describe('Test postHideUser', () => {
    it('should call useApi with correct parameters and not return an error', async () => {
        const { error, response } = await postHideUser('TestName123');

        expect(useApi).toHaveBeenCalledWith('POST', `api/costs/hideuser/TestName123`);
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
