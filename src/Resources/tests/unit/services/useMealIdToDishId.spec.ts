import { useMealIdToDishId } from '@/services/useMealIdToDishId';
import Participations from '../fixtures/menuParticipations.json';
import Weeks from '../fixtures/menuWeeks.json';
import Dishes from '../fixtures/menuDishes.json';
import { nextTick, ref } from 'vue';
import { flushPromises } from '@vue/test-utils';
import { vi, describe, it, expect } from 'vitest';

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

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

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
