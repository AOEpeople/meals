import postHideUser from '@/api/postHideUser';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';
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

describe('Test postHideUser', () => {
    it('should call useApi with correct parameters and not return an error', async () => {
        const { error, response } = await postHideUser('TestName123');

        expect(useApi).toHaveBeenCalledWith('POST', `api/costs/hideuser/TestName123`);
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
