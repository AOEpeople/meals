import useApi from "@/hooks/api";
import { ref } from "vue";

export type Meal = {
    id: number,
    title: { en: string, de: string }
    description: { en: string, de: string } | null
    limit: number,
    reachedLimit: boolean,
    isOpen: boolean,
    isLocked: boolean,
    isNew: boolean,
    price: number,
    participations: number,
    isParticipating: boolean,
    dishSlug: string
}

export type Meal_Variations = {
    title: { en: string, de: string }
    variations: Array<Meal>
}

export type DateTime = {
    date: string,
    timezone_type: number,
    timezone: string
}

export type Slot = {
    id: number,
    title: string,
    count: number,
    limit: number,
    slug: string | null
}

export type Day = {
    id: number,
    meals: Array<Meal | Meal_Variations>,
    date: DateTime,
    slots: Array<Slot>,
    activeSlot: number
}

export type Week = {
    id: number,
    days: Array<Day>,
}

export type Dashboard = {
    weeks: Array<Week>;
};


export async function useDashboardData() {
    const { response: dashboardData, request } = useApi<Dashboard>(
        "GET",
        "api/dashboard",
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { dashboardData };
}