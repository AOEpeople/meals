import { ref } from "vue";
import success from "../fixtures/createSlot.json";
import postDeleteSlot from "@/api/postDeleteSlot";
import { describe, expect, it } from "@jest/globals";
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

describe('Test postDeleteSlot', () => {
    it('should return a success on deleting a slot', async () => {
        const { error, response } = await postDeleteSlot(1);

        expect(useApi).toHaveBeenCalled();
        expect(error.value).toBeFalsy();
        expect(response.value.status).toEqual("success");
    });
});