import { useParticipationsListData } from '@/api/getParticipationsByDay';
import { ref } from 'vue';
import Participations from '../fixtures/participationsByDate.json';
import { vi, describe, it, expect } from 'vitest';

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
        const { useParticipationsError, listData, getListData } = await useParticipationsListData('2024-01-16');
        await getListData();
        expect(useParticipationsError.value).toBeFalsy();
        expect(listData.value).toEqual(Participations);
    });
});
