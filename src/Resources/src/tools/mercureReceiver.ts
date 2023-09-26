/**
 * Configure handlers to process meal push notifications.
 */
import { Meal } from '@/api/getDashboardData';
import { dashboardStore } from '@/stores/dashboardStore';
import { environmentStore } from '@/stores/environmentStore';

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
    }
}

const OFFER_NEW = 0
const OFFER_ACCEPTED = 1
const OFFER_GONE = 2

type Offer_Update = {
    type: number,
    weekId: number,
    dayId: number,
    mealId: number,
    parentId: number | null,
    participantId: number | null,
    lastOffer: boolean,
}

class MercureReceiver {
    public async init() {
        await this.configureMealUpdateHandlers()
    }

    private async configureMealUpdateHandlers(): Promise<void> {
        const eventSrc = new EventSource(environmentStore.getState().mercureUrl + '?topic=participation-updates&topic=meal-offer-updates&topic=slot-allocation-updates', {withCredentials: true})

        eventSrc.addEventListener('participationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleParticipationUpdate(JSON.parse(event.data))
        })

        eventSrc.addEventListener('mealOfferUpdate', (event: MessageEvent) => {
            MercureReceiver.handleMealOfferUpdate(JSON.parse(event.data))
        })

        eventSrc.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleSlotAllocationUpdate(JSON.parse(event.data))
        })
    }

    private static handleParticipationUpdate(data: Meal_Update): void {
        let meal;
        if(data.meal.parentId !== null) {
            meal = dashboardStore.getVariation(data.weekId, data.dayId, data.meal.parentId, data.meal.mealId) as Meal
        } else {
            meal = dashboardStore.getMeal(data.weekId, data.dayId, data.meal.mealId) as Meal
        }

        if(meal !== undefined) {
            meal.limit = data.meal.limit
            meal.participations = data.meal.participations
            meal.isOpen = data.meal.isOpen
            meal.isLocked = data.meal.isLocked
            meal.reachedLimit = data.meal.reachedLimit
        }
    }

    private static handleMealOfferUpdate(data: Offer_Update): void {
        const meal = data.parentId === null
            ? dashboardStore.getMeal(data.weekId, data.dayId, data.mealId)
            : dashboardStore.getVariation(data.weekId, data.dayId, data.parentId, data.mealId)

        if (meal !== undefined) {
            switch (data.type) {
                case OFFER_NEW:
                    meal.hasOffers = true
                    if (!meal.isParticipating) {
                        meal.mealState = 'tradeable'
                    }
                    break
                case OFFER_ACCEPTED:
                    if (data.participantId === meal.isParticipating) {
                        meal.isParticipating = null
                        if (data.lastOffer) {
                            meal.mealState = 'disabled'
                            meal.hasOffers = false
                        } else {
                            meal.mealState = 'tradeable'
                        }
                    }
                    break
                case OFFER_GONE:
                    meal.hasOffers = false
                    if (!meal.isParticipating) {
                        meal.mealState = 'disabled'
                    }
                    break
            }
        }
    }

    private static handleSlotAllocationUpdate(data: Slot_Update): void {
        const newSlot = dashboardStore.getSlot(data.weekId, data.dayId, data.newSlot.slotId)
        if (newSlot !== undefined) {
            newSlot.limit = data.newSlot.limit
            newSlot.count = data.newSlot.count
        }
        if (data.prevSlot.slotId !== 0) {
            const prevSlot = dashboardStore.getSlot(data.weekId, data.dayId, data.prevSlot.slotId)
            if (prevSlot !== undefined) {
                prevSlot.limit = data.prevSlot.limit
                prevSlot.count = data.prevSlot.count - 1
            }
        }
    }
}

export const mercureReceiver: MercureReceiver = new MercureReceiver()