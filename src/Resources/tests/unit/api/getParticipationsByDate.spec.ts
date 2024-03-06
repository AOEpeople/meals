import useApi from "@/api/api";
import { useParticipationsListData } from "@/api/getParticipationsByDay";
import { ref } from "vue";
import Participations from "../fixtures/participationsByDate.json";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Participations),
    request: asyncFunc,
    error: ref(false)
}

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getParticipations', () => {
    it('should return a list of participations', async () => {
        const {useParticipationsError, listData, getListData} = await useParticipationsListData("2024-01-16");
        await getListData();
        expect(useParticipationsError).toBeFalsy();
        expect(listData.value).toEqual(Participations);
    });
});