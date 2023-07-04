import { DateTime } from "@/api/getDashboardData";
import getWeeksData from "@/api/getWeeks";
import postCreateWeek from "@/api/postCreateWeek";
import putWeekUpdate from "@/api/putWeekUpdate";
import getDishCount from "@/api/getDishCount";
import { DayDTO, WeekDTO } from "@/interfaces/DayDTO";
import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";

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

    async function fetchWeeks() {
        WeeksState.isLoading = true;
        await getWeeks();
        WeeksState.isLoading = false;
    }

    async function getWeeks() {
        const { weeks, error } = await getWeeksData();
        if (!error.value && weeks.value) {
            WeeksState.weeks = weeks.value;
            WeeksState.error = '';
        } else {
            setTimeout(fetchWeeks, TIMEOUT_PERIOD);
            WeeksState.error = 'Error on getting the WeekData';
        }
    }

    async function createWeek(year: number, calendarWeek: number) {
        const { error, response } = await postCreateWeek(year, calendarWeek);

        if (error.value && response.value?.status !== 'success') {
            WeeksState.error = 'Error on creating the week';
            return;
        }

        await getWeeks();
    }

    async function updateWeek(week: WeekDTO) {

        const { error, response } = await putWeekUpdate(week);

        if (error.value || response.value?.status !== 'success') {
            WeeksState.error = 'Error on updating the week';
            return;
        }

        await getWeeks();
    }

    async function getDishCountForWeek() {
        const { error, response } = await getDishCount();

        if (error.value) {
            WeeksState.error = 'Error on getting the dish count';
            return;
        }

        if (response.value) {
            MenuCountState.counts = response.value;
        }
    }

    // TODO: Test this thouroghly
    function getDateRangeOfWeek(isoWeek: number, year: number) {
        const date = new Date(year, 0, 1 + (isoWeek - 1) * 7);
        const day = date.getDay();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - day + 1);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - day + 5);
        return [startDate, endDate];
    }

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

        if (week && day) {
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
        getDishCountForWeek
    }
}