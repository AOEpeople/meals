import { ref } from "vue";
import success from "../fixtures/Success.json";
import createSlot from "@/api/postCreateSlot";
import { describe, expect, it } from "@jest/globals";
import { TimeSlot } from "@/stores/timeSlotStore";
import useApi from "@/api/api";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(success),
    request: asyncFunc,
    error: ref(false)
}

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test postCreateSlot', () => {
    it('should return a success on creating a slot', async () => {
        const slot: TimeSlot = {
            title: "Test",
            limit: 0,
            order: 0,
            enabled: true
        }

        const { error, response } = await createSlot(slot);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value.status).toEqual("success");
    });
});