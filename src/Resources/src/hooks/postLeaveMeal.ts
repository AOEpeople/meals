import useApi from "@/hooks/api";
import { ref } from "vue";

export type LeaveMeal = {
    mealID: number
};

export async function useLeaveMeal(data: string) {
    const { response, request } = useApi<LeaveMeal>(
        "POST",
        "api/leave-meal",
        'application/json',
        data,
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { response };
}