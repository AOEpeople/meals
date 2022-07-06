import useApi from "@/hooks/api";
import { ref } from "vue";

export type JoinMeal = {
    mealID: number,
    dishSlugs: Array<string>,
    slotID: number,
};

export async function useJoinMeal(data: string) {

    const { response, request } = useApi<JoinMeal>(
        "POST",
        "api/join-meal",
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