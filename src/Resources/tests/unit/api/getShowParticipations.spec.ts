import { ref } from "vue";
import participations from "../fixtures/participations.json";
import { getShowParticipations } from "@/api/getShowParticipations";
import useApi from "@/api/api";
import { describe, it } from "@jest/globals";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(participations),
    request: asyncFunc,
    error: ref(false)
}

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getShowParticipations', () => {
    it('should fetch the participations and put them in the participationsState', async () => {
        const { participationsState, loadShowParticipations } = getShowParticipations();

        // inititial state should be empty
        expect(participationsState.data).toEqual({});
        expect(participationsState.meals).toEqual({});
        expect(participationsState.day.date).toBe("");
        expect(participationsState.day.timezone_type).toBe(0);
        expect(participationsState.day.timezone).toBe("");

        await loadShowParticipations();

        // participationsState should be filled after fetching
        expect(participations.data).toEqual(participationsState.data);
        expect(participations.meals).toEqual(participationsState.meals);
        expect(participations.day).toEqual(participationsState.day);
    });
});