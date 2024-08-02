import useApi from '@/api/api';
import postConfirmSettlement from '@/api/postConfirmSettlement';
import { ref } from 'vue';
import Profile from '../fixtures/hashProfile.json';
import { vi, describe, it, expect } from 'vitest';

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

describe('Test postConfirmSettlement', () => {
    it('should call useApi with the correct parameters', async () => {
        const { error, response } = await postConfirmSettlement(Profile.hash);

        expect(useApi).toHaveBeenCalledWith('POST', `api/costs/settlement/confirm/${Profile.hash}`);
        expect(error.value).toBe(false);
        expect(response.value).toBe(null);
    });
});
