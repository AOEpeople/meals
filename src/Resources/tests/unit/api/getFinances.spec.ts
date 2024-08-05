import getFinances from '@/api/getFinances';
import Finances from '../fixtures/finances.json';
import { ref } from 'vue';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Finances),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getFinances', () => {
    it('should return a list of finances that contain transactions', async () => {
        const { error, finances } = await getFinances();

        expect(error.value).toBeFalsy();
        expect(finances.value).toHaveLength(2);
        expect(finances.value[0].heading).toEqual('01.08. - 31.08.2023');
        expect(finances.value[0].transactions['2023-08-01']).toHaveLength(5);
    });
});
