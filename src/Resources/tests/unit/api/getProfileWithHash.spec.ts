import useApi from '@/api/api';
import getProfileWithHash from '@/api/getProfileWithHash';
import { ref } from 'vue';
import Profile from '../fixtures/hashProfile.json';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Profile.profile),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getProfileWithHash', () => {
    it('should return a profile', async () => {
        const { error, profile } = await getProfileWithHash(Profile.hash);

        expect(useApi).toHaveBeenCalledWith('GET', `api/costs/profile/${Profile.hash}`);
        expect(error.value).toBeFalsy();
        expect(profile.value).toEqual(Profile.profile);
    });
});
