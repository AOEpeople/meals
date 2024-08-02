import deleteDish from '@/api/deleteDish';
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

describe('Test deleteDish', () => {
    it('should return null', async () => {
        const { error, response } = await deleteDish('testen');

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(null);
    });
});
