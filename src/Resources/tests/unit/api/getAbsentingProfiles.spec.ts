import { ref } from 'vue';
import AbstainingProfiles from '../fixtures/abstaining.json';
import getAbsentingProfiles from '@/api/getAbsentingProfiles';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(AbstainingProfiles),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getAbsentingProfiles', () => {
    it('should return a list of abstaining Profiles', async () => {
        const { response, error } = await getAbsentingProfiles(1);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(AbstainingProfiles);
    });
});
