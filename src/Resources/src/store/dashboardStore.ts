import {Store} from '@/store/store';
import {useDashboardData, Day, Dashboard, Week, Meal, Meal_Variations} from '@/hooks/getDashboardData';

type Meal_Update = {
    dayId: number,
    meal: {
        mealId: number,
        limit: number,
        reachedLimit: boolean,
        isOpen: boolean,
        isLocked: boolean,
        participations: number,
    }
}

class DashboardStore extends Store<Dashboard> {

    protected data(): Dashboard {
        return {
            weeks: new Array<Week>(),
        }
    }

    async fillStore() {
        let { dashboardData } = await useDashboardData();
        if(dashboardData.value){
            this.state = dashboardData.value;
        } else {
            console.log('could not receive DashboardData')
        }

        this.configureMealUpdateHandlers()
    }

    public updateActiveSlotForDayById(id: number, newActiveSlot: number): void {
        let day = this.getDayById(id)
        day!.activeSlot = newActiveSlot
    }

    public getDayById(id: number): Day | null {
        let result = null

        this.state.weeks.forEach((week: Week) => {
            for (let day of week.days) {
                if (day.id === id) {
                    result = day
                    return day
                }
            }
            return null
        })

        return result
    }

    // @ts-ignore
    private static getMealByIdAndDay(id: number, day: Day): Meal | null {
        for (let meal_mealVariation of day.meals) {
            // @ts-ignore
            if(meal_mealVariation.variations) {
                // @ts-ignore
                for (let meal of meal_mealVariation.variations) {
                    if(meal.id === id) return meal
                }
            } else {
                // @ts-ignore
                if(meal_mealVariation.id === id) return meal_mealVariation
            }
        }

        return null
    }

    /**
     * Configure handlers to process meal push notifications.
     */
    private configureMealUpdateHandlers(): void {
        const eventSrc = new EventSource('https://meals.test:8081/.well-known/mercure?topic=participation-updates&topic=meal-offer-updates&topic=slot-allocation-updates', { withCredentials: true })

        // @ts-ignore
        eventSrc.addEventListener('participationUpdate', (event: MessageEvent) => {
            this.handleParticipationUpdate(JSON.parse(event.data))
        })
        // @ts-ignore
        eventSrc.addEventListener('mealOfferUpdate', (event: MessageEvent) => {
            this.handleMealOfferUpdate(JSON.parse(event.data))
        })
        // @ts-ignore
        eventSrc.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            this.handleSlotAllocationUpdate(JSON.parse(event.data))
        })
    }

    private handleParticipationUpdate(data: Meal_Update): void {
        let day = this.getDayById(data.dayId)
        if(day !== null) {
            let meal = DashboardStore.getMealByIdAndDay(data.meal.mealId, day)
            if(meal !== null) {
                meal.limit = data.meal.limit
                meal.participations = data.meal.participations
                meal.isOpen = data.meal.isOpen
                meal.isLocked = data.meal.isLocked
                meal.reachedLimit = data.meal.reachedLimit
            }
        }
    }
    private handleMealOfferUpdate(data: any): void {

    }
    private handleSlotAllocationUpdate(data: any): void {

    }
}

export const dashboardStore: DashboardStore = new DashboardStore()