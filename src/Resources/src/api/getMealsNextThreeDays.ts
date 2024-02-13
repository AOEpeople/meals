import { reactive, readonly } from 'vue';
import { Dictionary } from 'types/types';
import useApi from './api';
import { usePeriodicFetch } from '@/services/usePeriodicFetch';

interface INextThreeDaysState {
    days: IDay[];
    error: boolean;
}

export interface IMealList {
    en: string[];
    de: string[];
}

export interface IDay {
    en: string[];
    de: string[];
    date: Date;
}

// Sets the timeout period for getting the meals for the next three days in milliseconds (3600000 for one hour)
const PERIODIC_TIMEOUT = 3600000;

// timeout betweeen refetches if an error occures
const REFETCH_TIME_ON_ERROR = 10000;

const nextThreeDaysState = reactive<INextThreeDaysState>({
    days: [],
    error: false
});

/**
 * Performs a GET request to '/api/meals/nextThreeDays' and sets the dashBoardState accordingly
 */
async function fetchNextThreeDays() {
    const { response: daysData, request, error } = useApi<Dictionary<IMealList>>('GET', 'api/meals/nextThreeDays');

    await request();

    if (daysData.value !== null && daysData.value !== undefined && error.value === false) {
        nextThreeDaysState.error = false;
        nextThreeDaysState.days = convertMealsListToDay(daysData.value);
    } else {
        nextThreeDaysState.error = true;
        setTimeout(fetchNextThreeDays, REFETCH_TIME_ON_ERROR);
    }
}

function convertMealsListToDay(mealsList: Dictionary<IMealList>): IDay[] {
    const days: IDay[] = [];

    for (const [dateStr, meals] of Object.entries(mealsList)) {
        days.push({
            en: meals.en,
            de: meals.de,
            date: new Date(dateStr)
        });
    }

    return days.sort((dayOne, dayTwo) => dayOne.date.getTime() - dayTwo.date.getTime());
}

const { periodicFetchActive } = usePeriodicFetch(PERIODIC_TIMEOUT, fetchNextThreeDays);

export function getNextThreeDays() {
    function activatePeriodicFetch() {
        periodicFetchActive.value = true;
    }

    function disablePeriodicFetch() {
        periodicFetchActive.value = false;
    }

    /**
     * Only for testing state functionality
     */
    function resetState() {
        nextThreeDaysState.days = [];
        nextThreeDaysState.error = false;
    }

    return {
        nextThreeDaysState: readonly(nextThreeDaysState),
        activatePeriodicFetch,
        disablePeriodicFetch,
        fetchNextThreeDays,
        resetState
    };
}
