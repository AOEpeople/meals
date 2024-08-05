import Update from '../fixtures/participationUpdateResponse.json';
import { ref } from 'vue';
import putParticipation from '@/api/putParticipation';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Update.put),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test putParticipation', () => {
    it('should return an updated list of participations for the changed profile', async () => {
        const { response, error } = await putParticipation(1, '1');

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Update.put);
    });
});
