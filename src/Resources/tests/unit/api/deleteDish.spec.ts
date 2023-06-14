import deleteDish from "@/api/deleteDish";
import useApi from "@/api/api";
import success from "../fixtures/Success.json";
import { it, describe, expect } from "@jest/globals";
import { ref } from "vue";

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

describe('Test deleteDish', () => {
    it('should return a success object', async () => {
        const { error, response } = await deleteDish('testen');

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(success);
    });
});