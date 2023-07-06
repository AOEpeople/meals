import { useWeeks } from "@/stores/weeksStore";
import useApi from "@/api/api";
import { ref } from "vue";
import Success from "../fixtures/Success.json";
import Weeks from "../fixtures/getWeeks.json";
import DishesCount from "../fixtures/dishesCount.json";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/weeks') && method === 'GET') {
        return {
            response: ref(Weeks),
            request: asyncFunc,
            error: false
        }
    } else if (url.includes('api/meals/count') && method === 'GET') {
        return {
            response: ref(DishesCount),
            request: asyncFunc,
            error: false
        }
    } else if (url.includes('api/weeks/') && (method === 'POST')) {
        return {
            response: ref(Success),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('api/menu/') && method === 'PUT') {
        return {
            response: ref(Success),
            request: asyncFunc,
            error: ref(false)
        }
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test weeksStore', () => {
    const { WeeksState, MenuCountState, fetchWeeks, getDishCountForWeek, resetStates } = useWeeks();

    beforeEach(() => {
        resetStates();
    });

    it('should not contain data before fetching', () => {
        expect(WeeksState.weeks).toEqual([]);
        expect(WeeksState.isLoading).toBeFalsy();
        expect(WeeksState.error).toEqual('');
        expect(MenuCountState.counts).toEqual({});
    });

    it('should contain data after fetching', async () => {
        await fetchWeeks();
        expect(WeeksState.weeks).toEqual(Weeks);
        expect(WeeksState.isLoading).toBeFalsy();
        expect(WeeksState.error).toEqual('');

        await getDishCountForWeek();
        expect(MenuCountState.counts).toEqual(DishesCount);
    });
});