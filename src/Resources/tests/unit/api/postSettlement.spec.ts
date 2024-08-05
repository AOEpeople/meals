import postSettlement from '@/api/postSettlement';
import Profile from '../fixtures/hashProfile.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(null),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test postSettlement', () => {
    it('should return a response and should be called with a username', async () => {
        const { error, response } = await postSettlement(Profile.profile.user);

        expect(useApi).toHaveBeenCalledWith('POST', `api/costs/settlement/${Profile.profile.user}`);
        expect(error.value).toBe(false);
        expect(response.value).toBe(null);
    });
});
