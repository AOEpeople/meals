import useApi from '@/api/api';
import { type Dictionary } from '@/types/types';
import { ref } from 'vue';
import { MealState } from '@/enums/MealState';
import { Diet } from '@/enums/Diet';

export type Meal = {
    variations: Dictionary<Meal> | null;
    title: { en: string; de: string };
    description: { en: string; de: string } | null;
    limit: number | null;
    reachedLimit: boolean | null;
    isOpen: boolean;
    isLocked: boolean;
    isNew: boolean;
    price: number | null;
    participations: number | null;
    isParticipating: number | null;
    parentId: number | null;
    dishSlug: string | null;
    hasOffers: boolean;
    isOffering: boolean;
    mealState: MealState;
    diet: Diet;
};

export type DateTime = {
    date: string;
    timezone_type: number;
    timezone: string;
};

export type Slot = {
    id: number | string;
    title: string;
    count: number;
    limit: number;
    slug: string | null;
    disabled: boolean;
};

export type EventParticipation = {
    eventId: number;
    event: Event;
    day: Date;
    participationId: number;
    participations: number;
    isParticipating: boolean;
    isPublic: boolean;
};

export type Event = {
    id : number;
    title: string;
    slug: string;
    public: boolean;
};

export type Day = {
    date: DateTime;
    isLocked: boolean;
    activeSlot: number | string;
    meals: Dictionary<Meal>;
    slots: Dictionary<Slot>;
    slotsEnabled: boolean;
    isEnabled: boolean;
    events: Dictionary<EventParticipation>;
};

export type Week = {
    days: Dictionary<Day>;
    startDate: DateTime;
    endDate: DateTime;
    isEnabled: boolean;
};

export type Dashboard = {
    weeks: Dictionary<Week>;
};

export async function useDashboardData() {
    const { response: dashboardData, request } = useApi<Dashboard>('GET', 'api/dashboard');
    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { dashboardData };
}
