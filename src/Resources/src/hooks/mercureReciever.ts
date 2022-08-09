/**
 * Configure handlers to process meal push notifications.
 */
import {Meal} from "@/hooks/getDashboardData";
import {dashboardStore} from "@/store/dashboardStore";

type Meal_Update = {
    weekId: number,
    dayId: number,
    meal: {
        mealId: number,
        parentId: number,
        limit: number,
        reachedLimit: boolean,
        isOpen: boolean,
        isLocked: boolean,
        participations: number,
    }
}

type Slot_Update = {
    weekId: number,
    dayId: number,
    newSlot: {
        slotId: number,
        limit: number,
        count: number,
    },
    prevSlot: {
        slotId: number,
        limit: number,
        count: number,
    } | {}
}

class MercureReceiver {
    public init() {
        this.configureMealUpdateHandlers()
    }

    private configureMealUpdateHandlers(): void {
        const eventSrc = new EventSource('https://meals.test:8081/.well-known/mercure?topic=participation-updates&topic=meal-offer-updates&topic=slot-allocation-updates', { withCredentials: true })

        // @ts-ignore
        eventSrc.addEventListener('participationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleParticipationUpdate(JSON.parse(event.data))
        })
        // @ts-ignore
        eventSrc.addEventListener('mealOfferUpdate', (event: MessageEvent) => {
            MercureReceiver.handleMealOfferUpdate(JSON.parse(event.data))
        })
        // @ts-ignore
        eventSrc.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleSlotAllocationUpdate(JSON.parse(event.data))
        })
    }

    private static handleParticipationUpdate(data: Meal_Update): void {
        let meal = dashboardStore.getMeal(data.weekId, data.dayId, data.meal.mealId) as Meal
        if(meal !== undefined) {
            meal.limit = data.meal.limit
            meal.participations = data.meal.participations
            meal.isOpen = data.meal.isOpen
            meal.isLocked = data.meal.isLocked
            meal.reachedLimit = data.meal.reachedLimit
        }
    }

    private static handleMealOfferUpdate(data: any): void {

    }

    private static handleSlotAllocationUpdate(data: Slot_Update): void {
        let newSlot = dashboardStore.getSlot(data.weekId, data.dayId, data.newSlot.slotId)
        if (newSlot !== undefined) {
            newSlot.limit = data.newSlot.limit
            newSlot.count = data.newSlot.count
        }
    }
}

export const mercureReceiver: MercureReceiver = new MercureReceiver()