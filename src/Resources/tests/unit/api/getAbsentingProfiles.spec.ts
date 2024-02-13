import useApi from '@/api/api';
import { ref } from 'vue';
import AbstainingProfiles from '../fixtures/abstaining.json';
import getAbsentingProfiles from '@/api/getAbsentingProfiles';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(AbstainingProfiles),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getAbsentingProfiles', () => {
    it('should return a list of abstaining Profiles', async () => {
        const { response, error } = await getAbsentingProfiles(1);

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(AbstainingProfiles);
    });
});
