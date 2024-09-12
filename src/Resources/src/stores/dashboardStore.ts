import { Store } from '@/stores/store';
import { type Dashboard, type Day, type EventParticipation, type Meal, type Slot, useDashboardData, type Week } from '@/api/getDashboardData';
import { type Dictionary } from '@/types/types';
import { mercureReceiver } from '@/tools/mercureReceiver';
import useMealState from '@/services/useMealState';
import { MealState } from '@/enums/MealState';

class DashboardStore extends Store<Dashboard> {
    protected data(): Dashboard {
        return {
            weeks: {}
        };
    }

    async fillStore() {
        const { dashboardData } = await useDashboardData();
        if (dashboardData.value !== undefined && dashboardData.value !== null) {
            this.state = dashboardData.value;
            this.setMealStates(this.state);
        }

        await mercureReceiver.init();
    }

    public getWeek(weekID: number | string): Week | undefined {
        const week = this.state.weeks[weekID as number];
        return week;
    }

    public getWeeks(): Dictionary<Week> {
        return this.state.weeks;
    }

    public getDay(weekID: number | string, dayID: number | string): Day | undefined {
        const week = this.getWeek(weekID);
        if (week !== undefined) {
            const day = week.days[dayID as number];
            return day;
        }
        return undefined;
    }

    public getDayByEventParticipationId(participationId: number): Day | undefined {
        for (const week of Object.values(this.state.weeks)) {
            for (const day of Object.values(week.days)) {
                for (const iterator in Object.values(day.events )){
                    if( day.events[iterator].participationId === participationId){
                        return day;
                    }
                }
        }
        }
    }

    public getEventParticipationById(eventParticipationId: number): EventParticipation | undefined {
        for (const week of Object.values(this.state.weeks)) {
            for (const day of Object.values(week.days)) {
                for (const event of Object.values(day.events))
                    if (
                        event.eventId !== null &&
                        event.eventId !== undefined &&
                        event.eventId === eventParticipationId
                    ) {
                        return event;
                    }
            }
        }
    }

    public getDays(weekID: number | string): Dictionary<Day> | undefined {
        const week = this.getWeek(weekID);

        if (week !== undefined) {
            return week.days;
        }

        return undefined;
    }

    public getSlot(weekID: number | string, dayID: number | string, slotID: number | string): Slot | undefined {
        const day = this.getDay(weekID, dayID);
        if (day !== undefined) {
            const slot = day.slots[slotID as number];
            return slot;
        }

        return undefined;
    }

    public getMeal(weekID: number | string, dayID: number | string, mealID: number | string): Meal | undefined {
        const day = this.getDay(weekID, dayID);
        if (day !== undefined) {
            const meal = day.meals[mealID as number];
            return meal;
        }
        return undefined;
    }

    public getMeals(weekID: number | string, dayID: number | string): Dictionary<Meal> | undefined {
        const day = this.getDay(weekID, dayID);

        if (day !== undefined) {
            return day.meals;
        }
        return undefined;
    }

    public getVariation(
        weekID: number | string,
        dayID: number | string,
        parentMealID: number | string,
        variationID: number | string
    ): Meal | undefined {
        const parentMeal = this.getMeal(weekID, dayID, parentMealID);
        if (parentMeal !== undefined && parentMeal.variations !== null) {
            const variation = parentMeal.variations[variationID as number];
            return variation;
        }
        return undefined;
    }

    public updateEventParticipation(weekId: number, dayId: number, eventId: number, participations: number) {
        const day = this.getDay(weekId, dayId);
        if (day !== null && day !== undefined && day.events !== null ) {
            for (const iterator in Object.values(day.events )){
                if( day.events[iterator].eventId === eventId){
                    day.events[iterator].participations = participations;
                }
            }
        }
    }

    public setIsParticipatingEvent(participationId: number, isParticipating: boolean) {
        const day = this.getDayByEventParticipationId(participationId);
        console.log(day);
        if (day !== undefined) {
            for (const iterator in Object.values(day.events )){
                if( day.events[iterator].participationId === participationId){
                    day.events[iterator].isParticipating = isParticipating;
                }
            }
        }
    }

    public updateMealState(weekId: number, dayId: number, mealId: number, mealState: MealState) {
        const meal = this.getMeal(weekId, dayId, mealId);
        if (meal !== undefined && meal !== null) {
            meal.mealState = mealState;
        }
    }

    private setMealStates(dashboardData: Dashboard) {
        for (const week of Object.values(dashboardData.weeks)) {
            this.setMealStatesForWeek(week.days);
        }
    }

    private setMealStatesForVariations(meal: Meal) {
        const { generateMealState } = useMealState();

        if (meal.variations) {
            for (const variation of Object.values(meal.variations)) {
                variation.mealState = generateMealState(variation);
            }
        }
    }

    private setMealStatesForMeals(meals: Dictionary<Meal>) {
        const { generateMealState } = useMealState();

        for (const meal of Object.values(meals)) {
            if (meal.variations === null || meal.variations === undefined) {
                meal.mealState = generateMealState(meal);
            } else {
                this.setMealStatesForVariations(meal);
            }
        }
    }

    private setMealStatesForWeek(days: Dictionary<Day>) {
        for (const day of Object.values(days)) {
            this.setMealStatesForMeals(day.meals);
        }
    }

    /**
     * Only for testing purposes
     */
    public resetState() {
        this.state = {
            weeks: {}
        };
    }
}

export const dashboardStore: DashboardStore = new DashboardStore();
