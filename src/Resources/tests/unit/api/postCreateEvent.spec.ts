import useApi from '@/api/api';
import postCreateEvent from '@/api/postCreateEvent';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';

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

describe('Test postCreateEvent', () => {
    it('should call useApi and return null', async () => {
        const { error, response } = await postCreateEvent('test', true);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
