import useApi from '@/api/api';
import getProfileWithHash from '@/api/getProfileWithHash';
import { ref } from 'vue';
import Profile from '../fixtures/hashProfile.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Profile.profile),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getProfileWithHash', () => {
    it('should return a profile', async () => {
        const { error, profile } = await getProfileWithHash(Profile.hash);

        expect(useApi).toHaveBeenCalledWith('GET', `api/costs/profile/${Profile.hash}`);
        expect(error.value).toBeFalsy();
        expect(profile.value).toEqual(Profile.profile);
    });
});
