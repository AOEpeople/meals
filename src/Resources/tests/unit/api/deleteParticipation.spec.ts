import deleteParticipation from '@/api/deleteParticipation';
import { ref } from 'vue';
import Update from '../fixtures/participationUpdateResponse.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Update.delete),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test deleteParticipation', () => {
    it('should return an updated list of participations for the changed profile', async () => {
        const { response, error } = await deleteParticipation(1, '1');

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Update.delete);
    });
});
