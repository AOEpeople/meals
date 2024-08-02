import Participations from '../fixtures/menuParticipations.json';
import getParticipations from '@/api/getParticipations';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Participations),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getParticipations', () => {
    it('should return a list of participations', async () => {
        const { error } = await getParticipations(1);

        expect(error.value).toBeFalsy();
    });
});
