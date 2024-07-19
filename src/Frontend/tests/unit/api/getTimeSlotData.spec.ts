import { ref } from 'vue';
import TimeSlots from '../fixtures/getTimeSlots.json';
import { describe, it } from '@jest/globals';
import { useTimeSlotData } from '@/api/getTimeSlotData';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(TimeSlots.response),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getTimeSlotData', () => {
    it('should return a list of TimeSlots', async () => {
        const { timeslots, error } = await useTimeSlotData();

        expect(error.value).toBeFalsy();
        expect(timeslots.value).toEqual(TimeSlots.state);
    });
});
