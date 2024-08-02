import deleteDishVariation from '@/api/deleteDishVariation';
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

describe('Test deleteDishVariation', () => {
    it('should return null', async () => {
        const { error, response } = await deleteDishVariation('testvaren');

        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
