import {Store} from '@/store/store';
import {Dashboard, Day, Meal, Slot, useDashboardData, Week} from '@/hooks/getDashboardData';
import {Dictionary} from "../../types/types";
import {mercureReceiver} from "@/hooks/mercureReciever";

class DashboardStore extends Store<Dashboard> {

    protected data(): Dashboard {
        return {
            weeks: {},
        }
    }

    async fillStore() {
        let { dashboardData } = await useDashboardData()
        if(dashboardData.value){
            this.state = dashboardData.value
            console.log(this.state)
        } else {
            console.log('could not receive DashboardData')
        }

        mercureReceiver.init()
    }

    public getWeek(weekID: number | string): Week | undefined {
        let week = this.state.weeks[weekID as number];
        if(week === undefined) {
            console.log('week with ID: week: ' + weekID + ' not found')
        }
        return week
    }

    public getWeeks(): Dictionary<Week> {
        return this.state.weeks
    }

    public getDay(weekID: number | string, dayID: number | string): Day | undefined {
        let week = this.getWeek(weekID);
        if(week !== undefined) {
            let day = week.days[dayID as number]
            if (day === undefined) {
                console.log('day with ID: week: ' + weekID + ' day: '+ dayID + ' not found')
            }
            return day
        }
        return undefined
    }

    public getDays(weekID: number | string): Dictionary<Day> {
        let week = this.getWeek(weekID)
        return week!.days
    }

    public getSlot(weekID: number | string, dayID: number | string, slotID: number | string): Slot | undefined {
        let day = this.getDay(weekID, dayID)
        if(day !== undefined) {
            let slot = day.slots[slotID as number]
            if (slot === undefined) {
                console.log('slot with ID: week: ' + weekID + ' day: '+ dayID + ' slot: ' + slotID + ' not found')
            }
            return slot
        }
        return undefined
    }

    public getSlots(weekID: number | string, dayID: number | string): Dictionary<Slot> {
        let day = this.getDay(weekID, dayID)
        return day!.slots
    }

    public getMeal(weekID: number | string, dayID: number | string, mealID: number | string): Meal | undefined {
        let day = this.getDay(weekID, dayID)
        if(day !== undefined) {
            let meal = day.meals[mealID as number]
            if (meal === undefined) {
                console.log('meal with ID: week: ' + weekID + ' day: '+ dayID + ' meal: ' + mealID + ' not found')
            }
            return meal
        }
        return undefined
    }

    public getMeals(weekID: number | string, dayID: number | string): Dictionary<Meal> {
        let day = this.getDay(weekID, dayID)
        return day!.meals
    }

    public getVariation(weekID: number | string, dayID: number | string, parentMealID: number | string, variationID: number | string): Meal | undefined {
        let parentMeal = this.getMeal(weekID, dayID, parentMealID)
        if(parentMeal !== undefined) {
            let variation = parentMeal.variations![variationID as number]
            if (variation === undefined) {
                console.log('variation with ID: week: ' + weekID + ' day: '+ dayID + ' ParentMeal: ' + parentMealID + ' variation: ' + variationID + ' not found')
            }
            return variation
        }
        return undefined
    }

    public getVariations(weekID: number | string, dayID: number | string, parentMealID: number | string): Dictionary<Meal> {
        let parentMeal = this.getMeal(weekID, dayID, parentMealID)
        return parentMeal!.variations!
    }
}



export const dashboardStore: DashboardStore = new DashboardStore()