import { ref } from 'vue';
import deleteSlot from '@/api/deleteSlot';
import useApi from '@/api/api';
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

describe('Test postDeleteSlot', () => {
    it('should return null on deleting a slot', async () => {
        const { error, response } = await deleteSlot('1');

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
