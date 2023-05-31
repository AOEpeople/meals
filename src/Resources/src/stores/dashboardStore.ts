import {Store} from '@/stores/store';
import {Dashboard, Day, Meal, Slot, useDashboardData, Week} from '@/api/getDashboardData';
import {Dictionary} from "../../types/types";
import {mercureReceiver} from "tools/mercureReceiver";

class DashboardStore extends Store<Dashboard> {

    protected data(): Dashboard {
        return {
            weeks: {},
        }
    }

    async fillStore() {
        const { dashboardData } = await useDashboardData()
        if (dashboardData.value){
            this.state = dashboardData.value
        } else {
            console.log('could not receive DashboardData')
        }

        await mercureReceiver.init()
    }

    public getWeek(weekID: number | string): Week | undefined {
        const week = this.state.weeks[weekID as number];
        if (week === undefined) {
            console.log('week with ID: week: ' + weekID + ' not found')
        }
        return week
    }

    public getWeeks(): Dictionary<Week> {
        return this.state.weeks
    }

    public getDay(weekID: number | string, dayID: number | string): Day | undefined {
        const week = this.getWeek(weekID);
        if (week !== undefined) {
            const day = week.days[dayID as number]
            if (day === undefined) {
                console.log('day with ID: week: ' + weekID + ' day: '+ dayID + ' not found')
            }
            return day
        }
        return undefined
    }

    public getDays(weekID: number | string): Dictionary<Day> | undefined {
        const week = this.getWeek(weekID)

        if (week !== undefined) {
            return week.days
        }

        return undefined
    }

    public getSlot(weekID: number | string, dayID: number | string, slotID: number | string): Slot | undefined {
        const day = this.getDay(weekID, dayID)
        if (day !== undefined) {
            const slot = day.slots[slotID as number]
            if (slot === undefined) {
                console.log('getSlot: slot with ID ( week: ' + weekID + ' day: '+ dayID + ' slot: ' + slotID + ' ) not found')
            }
            return slot
        }

        return undefined
    }

    public getMeal(weekID: number | string, dayID: number | string, mealID: number | string): Meal | undefined {
        const day = this.getDay(weekID, dayID)
        if (day !== undefined) {
            const meal = day.meals[mealID as number]
            if (meal === undefined) {
                console.log('getMeal: meal with ID ( week: ' + weekID + ' day: '+ dayID + ' meal: ' + mealID + ' ) not found')
            }
            return meal
        }
        return undefined
    }

    public getMeals(weekID: number | string, dayID: number | string): Dictionary<Meal> | undefined {
        const day = this.getDay(weekID, dayID)

        if (day !== undefined) {
            return day.meals
        }
        return undefined
    }

    public getVariation(weekID: number | string, dayID: number | string, parentMealID: number | string, variationID: number | string): Meal | undefined {
        const parentMeal = this.getMeal(weekID, dayID, parentMealID)
        if (parentMeal !== undefined && parentMeal.variations !== null) {
            const variation = parentMeal.variations[variationID as number]
            if (variation === undefined) {
                console.log('getVariation: variation with ID ( week: ' + weekID + ' day: '+ dayID + ' ParentMeal: ' + parentMealID + ' variation: ' + variationID + ' ) not found')
            }
            return variation
        }
        return undefined
    }
}

export const dashboardStore: DashboardStore = new DashboardStore()