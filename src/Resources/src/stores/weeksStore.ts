import { type DateTime } from '@/api/getDashboardData';
import getWeeksData from '@/api/getWeeks';
import postCreateWeek from '@/api/postCreateWeek';
import putWeekUpdate from '@/api/putWeekUpdate';
import getDishCount from '@/api/getDishCount';
import type { DayDTO, WeekDTO } from '@/interfaces/DayDTO';
import { type Dictionary } from '@/types/types';
import { reactive, readonly, ref, watch } from 'vue';
import { isMessage, type IMessage } from '@/interfaces/IMessage';
import { isResponseArrayOkay } from '@/api/isResponseOkay';
import getEmptyWeek from '@/api/getEmptyWeek';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import getLockDatesForWeek from '@/api/getLockDatesForWeek';

export interface Week {
    id: number;
    year: number;
    calendarWeek: number;
    days: Dictionary<SimpleDay>;
    enabled: boolean;
}

export interface SimpleDay {
    dateTime: DateTime;
    lockParticipationDateTime: DateTime;
    week: number;
    meals: Dictionary<SimpleMeal[]>;
    event: number | null;
    enabled: boolean;
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
    weeks: Week[];
    isLoading: boolean;
    error: string;
}

interface MenuCountState {
    counts: Dictionary<number>;
}

/**
 * Checks if the given object is of type Week.
 * @param week The week to check.
 */
function isWeek(week: Week | undefined): week is Week {
    return (
        week !== null &&
        week !== undefined &&
        (typeof (week as Week).id === 'number' || (week as Week).id === null) &&
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

const { sendFlashMessage } = useFlashMessage();

watch(
    () => WeeksState.error,
    () => {
        if (WeeksState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: WeeksState.error
            });
        }
    }
);

export function useWeeks() {
    const lockDates = ref<Dictionary<DateTime> | null>(null);

    /**
     * Calls getWeeks() and sets the isLoading flag to true while the request is pending.
     */
    async function fetchWeeks() {
        WeeksState.isLoading = true;
        await getWeeks();
        WeeksState.isLoading = false;
    }

    async function fetchLockDatesForWeek(weekId: number) {
        const { error, response } = await getLockDatesForWeek(weekId);
        if (error.value === false) {
            lockDates.value = response.value ?? null;
        }
    }

    /**
     * Fetches the weeks from the backend and sets the WeeksState accordingly.
     */
    async function getWeeks() {
        const { weeks, error } = await getWeeksData();
        if (isResponseArrayOkay<Week>(error, weeks, isWeek) === true) {
            WeeksState.weeks = weeks.value as Week[];
            WeeksState.error = '';
        } else {
            setTimeout(fetchWeeks, TIMEOUT_PERIOD);
            WeeksState.error = 'Error on getting the WeekData';
        }
    }

    /**
     * Gets an empty week from the backend with basic information about the week.
     * Inserts the week into the state where it should be edited before it is send back to the backend for creation.
     * @param year          The year of the week
     * @param calendarWeek  The iso calendar week
     */
    async function createEmptyWeek(year: number, calendarWeek: number) {
        const { error, response } = await getEmptyWeek(year, calendarWeek);

        if (isMessage(response.value)) {
            WeeksState.error = response.value?.message;
            return;
        }

        if (error.value === false && isWeek(response.value)) {
            for (let i = 0; i < WeeksState.weeks.length; i++) {
                if (WeeksState.weeks[i].calendarWeek === response.value.calendarWeek) {
                    WeeksState.weeks[i] = response.value;
                    break;
                }
            }
        }

        return response.value?.calendarWeek;
    }

    /**
     * Tries to create a new week with the given year and calendarWeek.
     * If the request was successful, the weeks are fetched again and
     * the id of the newly created week is returned.
     * @param year          The year of the week to create.
     * @param calendarWeek  The calendar week of the week to create.
     * @param week          The week as it should be created
     */
    async function createWeek(year: number, calendarWeek: number, week: WeekDTO) {
        const { error, response } = await postCreateWeek(year, calendarWeek, week);

        if (error.value === true && isMessage(response.value)) {
            WeeksState.error = response.value?.message;
            return null;
        }

        await getWeeks();
        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'menu.created'
        });
        return response.value;
    }

    /**
     * Updates the given week i.e. the selected menu for the week.
     * @param week Data of the week to update.
     */
    async function updateWeek(week: WeekDTO) {
        const { error, response } = await putWeekUpdate(week);
        if (error.value === true || isMessage(response.value) === true) {
            WeeksState.error = (response.value as IMessage).message;
            return;
        }

        await getWeeks();
        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'menu.updated'
        });
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
     * @param dayId         The id of the day.
     * @param weekId        The id of the week, null if week is to be created.
     * @param calendarWeek  (optional) The iso week. Only used if the week is to be created.
     */
    function getMenuDay(dayId: string, weekId: number | null, calendarWeek?: number) {
        const week =
            weekId === null && calendarWeek !== undefined && calendarWeek !== null
                ? getWeekByCalendarWeek(calendarWeek)
                : getWeekById(weekId as number);
        const day = getDayById(week as Week, dayId);

        const menuDay: DayDTO = {
            meals: {},
            id: parseInt(dayId),
            enabled: day.enabled,
            event: day.event,
            date: day.dateTime,
            lockDate: day.lockParticipationDateTime
        };

        if (week !== undefined && week !== null && day !== undefined && day !== null) {
            for (const [key, meals] of Object.entries(day.meals)) {
                menuDay.meals[key] = meals.map((meal) => createMealDTO(meal));
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
        };
    }

    function getWeekById(weekId: number): Week | undefined {
        return WeeksState.weeks.find((week) => week.id === weekId);
    }

    function getDayById(week: Week, dayId: string) {
        return week.days[dayId];
    }

    function getDayByWeekIdAndDayId(weekId: number, dayId: string) {
        const week = getWeekById(weekId);
        if (week === undefined || week === null) return undefined;
        return getDayById(week, dayId);
    }

    function getWeekByCalendarWeek(isoWeek: number) {
        return WeeksState.weeks.find((week) => week.calendarWeek === isoWeek);
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
        lockDates,
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
        isWeek,
        createEmptyWeek,
        getWeekByCalendarWeek,
        fetchLockDatesForWeek
    };
}
