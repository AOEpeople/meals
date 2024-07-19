import useApi from '@/api/api';
import postSettlement from '@/api/postSettlement';
import Profile from '../fixtures/hashProfile.json';
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

describe('Test postSettlement', () => {
    it('should return a response and should be called with a username', async () => {
        const { error, response } = await postSettlement(Profile.profile.user);

        expect(useApi).toHaveBeenCalledWith('POST', `api/costs/settlement/${Profile.profile.user}`);
        expect(error.value).toBe(false);
        expect(response.value).toBe(null);
    });
});
