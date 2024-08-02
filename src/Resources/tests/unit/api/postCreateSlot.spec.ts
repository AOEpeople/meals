import { ref } from 'vue';
import createSlot from '@/api/postCreateSlot';
import { TimeSlot } from '@/stores/timeSlotStore';
import useApi from '@/api/api';
import { vi, describe, it, expect } from 'vitest';

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

describe('Test postCreateSlot', () => {
    it('should return null on creating a slot', async () => {
        const slot: TimeSlot = {
            title: 'Test',
            limit: 0,
            order: 0,
            enabled: true
        };

        const { error, response } = await createSlot(slot);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toBeNull();
    });
});
