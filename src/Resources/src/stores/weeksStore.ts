import { DateTime } from "@/api/getDashboardData";
import getWeeksData from "@/api/getWeeks";
import postCreateWeek from "@/api/postCreateWeek";
import { DayDTO, MealDTO } from "@/interfaces/DayDTO";
import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";

export interface Week {
    id: number,
    year: number,
    calendarWeek: number,
    days: Dictionary<SimpleDay>
}

export interface SimpleDay {
    id: number,
    dateTime: DateTime,
    lockParticipationDateTime: DateTime,
    week: number,
    meals: Dictionary<SimpleMeal>
}

export interface SimpleMeal {
    id: number;
    dish: string;
    participationLimit: number;
    day: number;
    dateTime: DateTime;
    lockTime: DateTime;
}

interface WeeksState {
    weeks: Week[],
    isLoading: boolean,
    error: string
}

const TIMEOUT_PERIOD = 10000;

const WeeksState = reactive<WeeksState>({
    weeks: [],
    isLoading: false,
    error: ''
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

    // TODO: Test this thouroghly
    function getDateRangeOfWeek(isoWeek: number, year: number) {
        const date = new Date(year, 0, 1 + (isoWeek - 1) * 7);
        const day = date.getDay();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - day + 1);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - day + 5);
        return [startDate, endDate];
    }

    function getMenuDay(weekId: number, dayId: string) {
        const menuDay: DayDTO = {
            meals: [],
            id: parseInt(dayId),
            enabled: true
        };
        const week = getWeekById(weekId);
        const day = getDayById(week, dayId);
        if (week && day) {
            for (const meal of Object.values(day.meals)) {
                menuDay.meals.push(createMealDTO(meal));
            }
        }
        return menuDay;
    }

    function createMealDTO(meal: SimpleMeal) {
        return {
            dishSlug: meal.dish,
            mealId: meal.id
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
        fetchWeeks,
        getDateRangeOfWeek,
        createWeek,
        getMenuDay
    }
}