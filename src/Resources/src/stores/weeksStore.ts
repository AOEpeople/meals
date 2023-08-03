import { DateTime } from "@/api/getDashboardData";
import getWeeksData from "@/api/getWeeks";
import postCreateWeek from "@/api/postCreateWeek";
import putWeekUpdate from "@/api/putWeekUpdate";
import getDishCount from "@/api/getDishCount";
import { DayDTO, WeekDTO } from "@/interfaces/DayDTO";
import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";
import { isMessage } from "@/interfaces/IMessage";
import { isResponseArrayOkay } from "@/api/isResponseOkay";

export interface Week {
    id: number,
    year: number,
    calendarWeek: number,
    days: Dictionary<SimpleDay>,
    enabled: boolean
}

export interface SimpleDay {
    id: number,
    dateTime: DateTime,
    lockParticipationDateTime: DateTime,
    week: number,
    meals: Dictionary<SimpleMeal[]>,
    enabled: boolean
}

export interface SimpleMeal {
    id: number;
    dish: string;
    participationLimit: number;
    day: number;
    dateTime: DateTime;
    lockTime: DateTime;
    count: number | null;
}

interface WeeksState {
    weeks: Week[],
    isLoading: boolean,
    error: string
}

interface MenuCountState {
    counts: Dictionary<number>
}

/**
 * Checks if the given object is of type Week.
 * @param week The week to check.
 */
function isWeek(week: Week): week is Week {
    return (
        week !== null &&
        week !== undefined &&
        typeof (week as Week).id === 'number' &&
        typeof (week as Week).enabled === 'boolean' &&
        typeof (week as Week).calendarWeek === 'number' &&
        typeof (week as Week).year === 'number' &&
        typeof (week as Week).days === 'object' &&
        Object.keys(week).length === 5
    );
}

const TIMEOUT_PERIOD = 10000;

const WeeksState = reactive<WeeksState>({
    weeks: [],
    isLoading: false,
    error: ''
});

const MenuCountState = reactive<MenuCountState>({
    counts: {}
});

export function useWeeks() {

    /**
     * Calls getWeeks() and sets the isLoading flag to true while the request is pending.
     */
    async function fetchWeeks() {
        WeeksState.isLoading = true;
        await getWeeks();
        WeeksState.isLoading = false;
    }

    /**
     * Fetches the weeks from the backend and sets the WeeksState accordingly.
     */
    async function getWeeks() {
        const { weeks, error } = await getWeeksData();
        if (isResponseArrayOkay<Week>(error, weeks, isWeek) === true) {
            WeeksState.weeks = weeks.value;
            WeeksState.error = '';
        } else {
            setTimeout(fetchWeeks, TIMEOUT_PERIOD);
            WeeksState.error = 'Error on getting the WeekData';
        }
    }

    /**
     * Tries to create a new week with the given year and calendarWeek.
     * If the request was successful, the weeks are fetched again.
     * @param year          The year of the week to create.
     * @param calendarWeek  The calendar week of the week to create.
     */
    async function createWeek(year: number, calendarWeek: number) {
        const { error, response } = await postCreateWeek(year, calendarWeek);

        if (error.value === true && isMessage(response.value) === true) {
            WeeksState.error = response.value?.message;
            return;
        }

        await getWeeks();
    }

    /**
     * Updates the given week i.e. the selected menu for the week.
     * @param week Data of the week to update.
     */
    async function updateWeek(week: WeekDTO) {

        const { error, response } = await putWeekUpdate(week);

        if (error.value === true || isMessage(response.value) === true) {
            WeeksState.error = response.value?.message;
            return;
        }

        await getWeeks();
    }

    /**
     * Fetches the number of times each dish is booked for the current week.
     */
    async function getDishCountForWeek() {
        const { error, response } = await getDishCount();

        if (error.value === true || isMessage(response.value)) {
            WeeksState.error = response.value?.message as string;
            return;
        } else if (response.value !== undefined && response.value !== null) {
            MenuCountState.counts = response.value;
        }
    }

    /**
     * Gets the start and end date for the given week.
     * @param isoWeek   The calendar week.
     * @param year      The year.
     */
    function getDateRangeOfWeek(isoWeek: number, year: number) {
        const date = new Date(year, 0, 1 + (isoWeek - 1) * 7);
        const day = date.getDay();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - day + 1, 12);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - day + 5, 12);
        return [startDate, endDate];
    }

    /**
     * Returns all the relevant data for a day.
     * @param weekId    The id of the week.
     * @param dayId     The id of the day.
     */
    function getMenuDay(weekId: number, dayId: string) {
        const week = getWeekById(weekId);
        const day = getDayById(week, dayId);

        const menuDay: DayDTO = {
            meals: {},
            id: parseInt(dayId),
            enabled: day.enabled,
            date: day.dateTime,
            lockDate: day.lockParticipationDateTime
        };

        if (week !== undefined && week !== null && day !== undefined && day !== null) {
            for (const [key, meals] of Object.entries(day.meals)) {
                menuDay.meals[key] = meals.map(meal => createMealDTO(meal));
            }
            // make sure to have 2 meals
            const mealsLength = Object.keys(menuDay.meals).length;
            if (mealsLength < 2) {
                for (let i = mealsLength; i < 2; i++) {
                    // use negative number to display the meal is to be created
                    menuDay.meals[-i] = [];
                }
            }
        }
        return menuDay;
    }

    /**
     * Creates a MealDTO for a meal.
     * @param meal  The meal to create the DTO for.
     */
    function createMealDTO(meal: SimpleMeal) {
        return {
            dishSlug: meal.dish,
            mealId: meal.id,
            participationLimit: meal.participationLimit
        }
    }

    function getWeekById(weekId: number): Week | undefined {
        return WeeksState.weeks.find(week => week.id === weekId);
    }

    function getDayById(week: Week, dayId: string) {
        return week.days[dayId];
    }

    function getDayByWeekIdAndDayId(weekId: number, dayId: string) {
        return getDayById(getWeekById(weekId), dayId);
    }

    /**
     * Resets the DishesState.
     * Only used for testing purposes.
     */
    function resetStates() {
        WeeksState.weeks = [];
        WeeksState.error = '';
        WeeksState.isLoading = false;
        MenuCountState.counts = {};
    }

    return {
        WeeksState: readonly(WeeksState),
        MenuCountState: readonly(MenuCountState),
        fetchWeeks,
        getDateRangeOfWeek,
        createWeek,
        getMenuDay,
        createMealDTO,
        getWeekById,
        updateWeek,
        getDishCountForWeek,
        resetStates,
        getDayByWeekIdAndDayId,
        isWeek
    }
}