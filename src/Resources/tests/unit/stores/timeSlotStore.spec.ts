import { useTimeSlots } from "@/stores/timeSlotStore";
import { beforeEach, describe } from "@jest/globals";
import { ref } from "vue";
import updatedSlot from "../fixtures/updatedSlot.json";
import success from "../fixtures/createSlot.json";
import timeSlots from "../fixtures/getTimeSlots.json";
import useApi from "@/api/api";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (url: string) => {
    switch(url) {
        case "api/create-slot" || "api/delete-slot":
            return {
                response: ref(success),
                request: asyncFunc,
                error: ref(false)
            };
        case "api/update-slot":
            return {
                response: ref(updatedSlot),
                request: asyncFunc,
                error: ref(false)
            }
        case "api/timeslots":
            return {
                response: ref(timeSlots),
                request: asyncFunc,
                error: false
            }
        default:
            return {}
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(url));


describe('Test timeSlotStore', () => {

    const { TimeSlotState, resetState, fetchTimeSlots, editSlot } = useTimeSlots();

    beforeEach(() => {
        resetState();
    })

    it('should not contain slot data before fetching', () => {
        expect(TimeSlotState.timeSlots).toEqual({});
        expect(TimeSlotState.error).toEqual("");
        expect(TimeSlotState.isLoading).toBeFalsy();
    });

    it('should contain the the timeslots from the fixture after fetching', async () => {
        await fetchTimeSlots();

        expect(TimeSlotState.timeSlots).toEqual(timeSlots);
        expect(TimeSlotState.error).toEqual("");
        expect(TimeSlotState.isLoading).toBeFalsy();
    });

    it('should update the slotdata in the state', async () => {
        await fetchTimeSlots();

        expect(TimeSlotState.timeSlots['13']).not.toEqual(updatedSlot);

        await editSlot(13, updatedSlot);

        expect(TimeSlotState.timeSlots['13']).toEqual(updatedSlot);
    });
});