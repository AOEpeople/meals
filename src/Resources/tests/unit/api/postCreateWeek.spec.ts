import postCreateWeek from "@/api/postCreateWeek";
import { ref } from "vue";
import Success from "../fixtures/Success.json";
import useApi from "@/api/api";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Success),
    request: asyncFunc,
    error: ref(false)
}

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test postCreateWeek', () => {
    it('should create a new week', async () => {
        const { error, response } = await postCreateWeek(2023, 20);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Success);
    });
});