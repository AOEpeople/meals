import { ref } from 'vue';
import useApi from '@/api/api';
import deleteEvent from '@/api/deleteEvent';
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

describe('Test deleteEvent', () => {
    it('should call useApi and return null', async () => {
        const { error, response } = await deleteEvent('test');

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
