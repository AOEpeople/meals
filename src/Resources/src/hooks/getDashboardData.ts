import useApi from "@/hooks/api";
import { ref } from "vue";

type Meal = {
    id: number,
    title: { en: string, de: string }
    description: { en: string, de: string } | null
    limit: number,
    reachedLimit: boolean,
    isOpen: boolean,
    isLocked: boolean,
    price: number,
    participations: number,
    isParticipating: boolean
}

type Meal_Variations = {
    title: { en: string, de: string }
    variations: Array<Meal>
}

type DateTime = {
    date: string,
    timezone_type: number,
    timezone: string
}

type Day = {
    id: number,
    meals: Array<Meal | Meal_Variations>,
    date: DateTime,
    slots: []
}

export type Dashboard = {
    week: [{
        id: number,
        days: Array<Day>,
    }];
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