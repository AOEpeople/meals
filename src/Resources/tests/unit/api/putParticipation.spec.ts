import useApi from '@/api/api';
import Update from '../fixtures/participationUpdateResponse.json';
import { ref } from 'vue';
import putParticipation from '@/api/putParticipation';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Update.put),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test putParticipation', () => {
    it('should return an updated list of participations for the changed profile', async () => {
        const { response, error } = await putParticipation(1, '1');

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Update.put);
    });
});
