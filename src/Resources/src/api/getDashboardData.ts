import useApi from "@/api/api";
import { Dictionary } from "../../types/types";
import { reactive, readonly, ref } from "vue";
import { usePeriodicFetch } from "@/services/usePeriodicFetch";

export type Meal = {
    variations: Dictionary<Meal> | null
    title: { en: string, de: string },
    description: { en: string, de: string } | null,
    limit: number | null,
    reachedLimit: boolean | null,
    isOpen: boolean,
    isLocked: boolean,
    isNew: boolean,
    price: number | null,
    participations: number | null,
    isParticipating: number | null,
    parentId: number | null,
    dishSlug: string | null,
    hasOffers: boolean,
    isOffering: boolean,
    mealState: string,
}

export type DateTime = {
    date: string,
    timezone_type: number,
    timezone: string
}

export type Slot = {
    id: number | string,
    title: string,
    count: number,
    limit: number,
    slug: string | null,
    disabled: boolean
}

export type Day = {
    date: DateTime,
    isLocked: boolean,
    activeSlot: number | string,
    meals: Dictionary<Meal>,
    slots: Dictionary<Slot>,
    slotsEnabled: boolean,
}

export type Week = {
    days: Dictionary<Day>,
    startDate: DateTime,
    endDate: DateTime
}

export type Dashboard = {
    weeks: Dictionary<Week>
}

// Sets the timeout period for getting the meals for the next three days in milliseconds (3600000 for one hour)
const PERIODIC_TIMEOUT = 3600000;

// timeout betweeen refetches if an error occures
const REFETCH_TIME_ON_ERROR = 10000;

const dashBoardState = reactive<Dashboard>({
    weeks: {}
});

const errorState = ref(false);

/**
 * Performs a GET request to '/api/dashboard' and sets the dashBoardState accordingly
 */
async function getDashboard() {
    const { response: dashboardData, request, error } = useApi<Dashboard>(
        "GET",
        "api/dashboard",
    );

    await request();

    if(dashboardData.value && !error.value) {
        errorState.value = false;
        dashBoardState.weeks = dashboardData.value.weeks;
    } else {
        errorState.value = true;
        setTimeout(getDashboard, REFETCH_TIME_ON_ERROR);
    }
}

const { periodicFetchActive } = usePeriodicFetch(PERIODIC_TIMEOUT, getDashboard);

export function getDashboardData() {

    function activatePeriodicFetch() {
        periodicFetchActive.value = true;
    }

    function disablePeriodicFetch() {
        periodicFetchActive.value = false;
    }

    /**
     * Returns a list of dates for the next three days with meals, starting from the day after the passed in date
     * @param dayDate date to start the search for new dates from
     * @returns a list of dates
     */
    function getNextThreeDays(dayDate: Date): Day[] {
        const nextThreeDays: Day[] = [];
        for(const week of Object.values(dashBoardState.weeks)) {
            for(const day of Object.values(week.days)) {
                const date = new Date(day.date.date);
                if(date.getTime() > dayDate.getTime()) {
                    nextThreeDays.push(day);
                }
                if(nextThreeDays.length >= 3) {
                    return nextThreeDays;
                }
            }
        }
        return nextThreeDays;
    }

    return {
        dashBoardState: readonly(dashBoardState),
        errorState: readonly(errorState),
        getDashboard,
        activatePeriodicFetch,
        disablePeriodicFetch,
        getNextThreeDays
    }
}

export async function useDashboardData() {
    const { response: dashboardData, request } = useApi<Dashboard>(
        "GET",
        "api/dashboard",
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { dashboardData };
}