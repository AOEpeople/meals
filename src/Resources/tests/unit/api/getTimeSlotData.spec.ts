import { ref } from 'vue';
import TimeSlots from '../fixtures/getTimeSlots.json';
import { useTimeSlotData } from '@/api/getTimeSlotData';
import { vi, expect, describe, it } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(TimeSlots.response),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getTimeSlotData', () => {
    it('should return a list of TimeSlots', async () => {
        const { timeslots, error } = await useTimeSlotData();

        expect(error.value).toBeFalsy();
        expect(timeslots.value).toEqual(TimeSlots.state);
    });
});
