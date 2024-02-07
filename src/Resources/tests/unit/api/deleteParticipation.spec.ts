import deleteParticipation from '@/api/deleteParticipation';
import { ref } from 'vue';
import useApi from '@/api/api';
import Update from '../fixtures/participationUpdateResponse.json';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Update.delete),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test deleteParticipation', () => {
    it('should return an updated list of participations for the changed profile', async () => {
        const { response, error } = await deleteParticipation(1, '1');

        expect(error.value).toBeFalsy();
        expect(response.value).toEqual(Update.delete);
    });
});
