import { ref } from 'vue';
import updatedSlot from '../fixtures/updatedSlot.json';
import { useUpdateSlot } from '@/api/putSlotUpdate';
import { vi, describe, it, expect } from 'vitest';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(updatedSlot.response),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test postSlotUpdate', () => {
    const { updateSlotEnabled, updateTimeSlot } = useUpdateSlot();

    it('should return the changed slot', async () => {
        const { error, response } = await updateTimeSlot(updatedSlot.state);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(updatedSlot.response);
    });

    it('should return the changed slot', async () => {
        const { error, response } = await updateSlotEnabled('1', true);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(updatedSlot.response);
    });
});
