import { useMealIdToDishId } from '@/services/useMealIdToDishId';
import Participations from '../fixtures/menuParticipations.json';
import Weeks from '../fixtures/menuWeeks.json';
import Dishes from '../fixtures/menuDishes.json';
import { nextTick, ref } from 'vue';
import useApi from '@/api/api';
import { flushPromises } from '@vue/test-utils';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/participations\/\d+$/.test(url) === true && method === 'GET') {
        return {
            response: ref(Participations),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/weeks') && method === 'GET') {
        return {
            response: ref(Weeks),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/dishes') && method === 'GET') {
        return {
            response: ref(Dishes),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test useMealIdToDishId', () => {
    it('should return the dish id for a given meal id', async () => {
        const { mealIdToDishIdDict } = useMealIdToDishId(115);

        await flushPromises();
        setTimeout(async () => {
            await nextTick();
            expect(Object.keys(mealIdToDishIdDict).length).toBe(11);
            expect(mealIdToDishIdDict.get(1523)).toBe(50);
        }, 1000);
    });
});
