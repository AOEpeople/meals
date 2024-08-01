import { useTimeSlots } from '@/stores/timeSlotStore';
import { ref } from 'vue';
import updatedSlot from '../fixtures/updatedSlot.json';
import timeSlots from '../fixtures/getTimeSlots.json';
import useApi from '@/api/api';
import { describe, beforeEach, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/slots') && method === 'GET') {
        return {
            response: ref(timeSlots.response),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/slot') && (method === 'POST' || method === 'DELETE')) {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/slot') && method === 'PUT') {
        return {
            response: ref(updatedSlot.response),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test timeSlotStore', () => {
    const { TimeSlotState, resetState, fetchTimeSlots, editSlot } = useTimeSlots();

    beforeEach(() => {
        resetState();
    });

    it('should not contain slot data before fetching', () => {
        expect(TimeSlotState.timeSlots).toEqual({});
        expect(TimeSlotState.error).toEqual('');
        expect(TimeSlotState.isLoading).toBeFalsy();
    });

    it('should contain the the timeslots from the fixture after fetching', async () => {
        await fetchTimeSlots();

        expect(TimeSlotState.timeSlots).toEqual(timeSlots.state);
        expect(TimeSlotState.error).toEqual('');
        expect(TimeSlotState.isLoading).toBeFalsy();
    });

    it('should update the slotdata in the state', async () => {
        await fetchTimeSlots();

        expect(TimeSlotState.timeSlots[17]).not.toEqual(updatedSlot.state);

        await editSlot(17, updatedSlot.state);

        expect(TimeSlotState.timeSlots[17]).toEqual(updatedSlot.state);
    });
});
