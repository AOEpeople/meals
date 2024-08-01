import { useWeeks } from '@/stores/weeksStore';
import useApi from '@/api/api';
import { ref } from 'vue';
import Weeks from '../fixtures/getWeeks.json';
import DishesCount from '../fixtures/dishesCount.json';
import { describe, beforeEach, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/weeks') && method === 'GET') {
        return {
            response: ref(Weeks),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/meals/count') && method === 'GET') {
        return {
            response: ref(DishesCount),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/weeks/') && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/menu/') && method === 'PUT') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test weeksStore', () => {
    const {
        WeeksState,
        MenuCountState,
        fetchWeeks,
        getDishCountForWeek,
        resetStates,
        getDateRangeOfWeek,
        getMenuDay,
        getWeekById
    } = useWeeks();

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

    it('should return a start date on Monday and end date on Friday within the same week', () => {
        const weekOne = [27, 2023];
        const weekTwo = [28, 2023];
        const weekThree = [29, 2023];

        const weekOneDates = getDateRangeOfWeek(weekOne[0], weekOne[1]);
        const weekTwoDates = getDateRangeOfWeek(weekTwo[0], weekTwo[1]);
        const weekThreeDates = getDateRangeOfWeek(weekThree[0], weekThree[1]);

        expect(weekOneDates[0].toISOString()).toEqual(new Date('2023-07-03T12:00:00.000').toISOString());
        expect(weekOneDates[1].toISOString()).toEqual(new Date('2023-07-07T12:00:00.000').toISOString());
        expect(weekTwoDates[0].toISOString()).toEqual(new Date('2023-07-10T12:00:00.000').toISOString());
        expect(weekTwoDates[1].toISOString()).toEqual(new Date('2023-07-14T12:00:00.000').toISOString());
        expect(weekThreeDates[0].toISOString()).toEqual(new Date('2023-07-17T12:00:00.000').toISOString());
        expect(weekThreeDates[1].toISOString()).toEqual(new Date('2023-07-21T12:00:00.000').toISOString());
    });

    it('should return the right menuday for a given weekId and dayId', async () => {
        await fetchWeeks();

        const ids = { week: 57, day: '281' };

        const menuDay = getMenuDay(ids.day, ids.week);

        expect(menuDay.id).toBe(parseInt(ids.day));
        expect(menuDay.date.date).toEqual('2023-07-03 12:00:00.000000');
        expect(menuDay.enabled).toBeTruthy();
        expect(menuDay.lockDate.date).toEqual('2023-07-02 16:00:00.000000');
    });

    it('should return the right week for a given weekId', async () => {
        await fetchWeeks();

        const week = getWeekById(57);

        expect(week.id).toBe(57);
        expect(week.year).toBe(2023);
        expect(week.calendarWeek).toBe(27);
    });
});
