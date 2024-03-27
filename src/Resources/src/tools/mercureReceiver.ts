/**
 * Configure handlers to process meal push notifications.
 */
import { Meal } from '@/api/getDashboardData';
import { dashboardStore } from '@/stores/dashboardStore';
import { environmentStore } from '@/stores/environmentStore';
import { useLockRequests } from '@/services/useLockRequests';
import getMealState from '@/api/getMealState';

type Meal_Update = {
    weekId: number;
    dayId: number;
    meals: Meal_Participation_Update[];
};

type Meal_Participation_Update = {
    mealId: number;
    parentId: number;
    limit: number;
    reachedLimit: boolean;
    isOpen: boolean;
    isLocked: boolean;
    participations: number;
};

type Event_Participation_Update = {
    weekId: number;
    dayId: number;
    event: {
        eventId: number;
        participations: number;
    };
};

type Slot_Update = {
    weekId: number;
    dayId: number;
    newSlot: {
        slotId: number;
        limit: number;
        count: number;
    };
    prevSlot: {
        slotId: number;
        limit: number;
        count: number;
    };
};

const OFFER_NEW = 0;
const OFFER_ACCEPTED = 1;
const OFFER_GONE = 2;
const { removeLock } = useLockRequests();

type Offer_Update = {
    type: number;
    weekId: number;
    dayId: number;
    mealId: number;
    parentId: number | null;
    participantId: number | null;
    lastOffer: boolean;
};

class MercureReceiver {
    public async init() {
        await this.configureMealUpdateHandlers();
    }

    private async configureMealUpdateHandlers(): Promise<void> {
        const eventSrc = new EventSource(
            environmentStore.getState().mercureUrl +
                '?topic=participation-updates&topic=meal-offer-updates&topic=slot-allocation-updates&topic=event-participation-updates&topic=keep-alive-connection',
            { withCredentials: true }
        );

        eventSrc.addEventListener('participationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleParticipationUpdate(JSON.parse(event.data));
        });

        eventSrc.addEventListener('mealOfferUpdate', (event: MessageEvent) => {
            MercureReceiver.handleMealOfferUpdate(JSON.parse(event.data));
        });

        eventSrc.addEventListener('slotAllocationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleSlotAllocationUpdate(JSON.parse(event.data));
        });

        eventSrc.addEventListener('eventParticipationUpdate', (event: MessageEvent) => {
            MercureReceiver.handleEventParticipationUpdate(JSON.parse(event.data));
        });
    }

    private static handleEventParticipationUpdate(data: Event_Participation_Update) {
        dashboardStore.updateEventParticipation(data.weekId, data.dayId, data.event.eventId, data.event.participations);
        removeLock(String(data.dayId));
    }

    private static async handleParticipationUpdate(data: Meal_Update): Promise<void> {
        for (const mealData of data.meals) {
            const { error, mealstate } = await getMealState(mealData.mealId);
            let meal: Meal;
            if (mealData.parentId !== null) {
                meal = dashboardStore.getVariation(data.weekId, data.dayId, mealData.parentId, mealData.mealId) as Meal;
            } else {
                meal = dashboardStore.getMeal(data.weekId, data.dayId, mealData.mealId) as Meal;
            }

            if (meal !== undefined) {
                meal.limit = mealData.limit;
                meal.participations = mealData.participations;
                meal.isOpen = mealData.isOpen;
                meal.isLocked = mealData.isLocked;
                meal.reachedLimit = mealData.reachedLimit;
                if (error.value === false) {
                    meal.mealState = mealstate.value;
                }
            }
        }
        removeLock(String(data.dayId));
    }

    private static handleMealOfferUpdate(data: Offer_Update): void {
        const meal =
            data.parentId === null
                ? dashboardStore.getMeal(data.weekId, data.dayId, data.mealId)
                : dashboardStore.getVariation(data.weekId, data.dayId, data.parentId, data.mealId);

        if (meal !== undefined) {
            switch (data.type) {
                case OFFER_NEW:
                    meal.hasOffers = true;
                    if (!meal.isParticipating) {
                        meal.mealState = 'tradeable';
                    }
                    break;
                case OFFER_ACCEPTED:
                    if (data.participantId === meal.isParticipating) {
                        meal.isParticipating = null;
                        if (data.lastOffer) {
                            meal.mealState = 'disabled';
                            meal.hasOffers = false;
                        } else {
                            meal.mealState = 'tradeable';
                        }
                    }
                    break;
                case OFFER_GONE:
                    meal.hasOffers = false;
                    if (!meal.isParticipating) {
                        meal.mealState = 'disabled';
                    }
                    break;
            }
        }
        removeLock(String(data.dayId));
    }

    private static handleSlotAllocationUpdate(data: Slot_Update): void {
        const newSlot = dashboardStore.getSlot(data.weekId, data.dayId, data.newSlot.slotId);
        if (newSlot !== undefined) {
            newSlot.limit = data.newSlot.limit;
            newSlot.count = data.newSlot.count;
        }
        if (data.prevSlot.slotId !== 0) {
            const prevSlot = dashboardStore.getSlot(data.weekId, data.dayId, data.prevSlot.slotId);
            if (prevSlot !== undefined) {
                prevSlot.limit = data.prevSlot.limit;
                prevSlot.count = data.prevSlot.count - 1;
            }
        }
    }
}

export const mercureReceiver: MercureReceiver = new MercureReceiver();
