import { ref } from 'vue';
import updatedSlot from '../fixtures/updatedSlot.json';
import { useUpdateSlot } from '@/api/putSlotUpdate';
import { describe, expect, it } from '@jest/globals';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(updatedSlot.response),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

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
