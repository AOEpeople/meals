import { ref } from 'vue';
import participations from '../fixtures/participations.json';
import getPrintParticipations from '@/api/getPrintParticipations';
import { vi, expect, describe, it } from 'vitest';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(participations),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getPrintParticipations', () => {
    it('should return participations data', async () => {
        const { response, error } = await getPrintParticipations();

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(participations);
    });
});