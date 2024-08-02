import getWeeks from '@/api/getWeeks';
import { ref } from 'vue';
import Weeks from '../fixtures/getWeeks.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Weeks),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getWeeks', () => {
    it('should return a list of Weeks', async () => {
        const { weeks, error } = await getWeeks();

        expect(error.value).toBeFalsy();
        expect(weeks.value).toEqual(Weeks);
    });
});
