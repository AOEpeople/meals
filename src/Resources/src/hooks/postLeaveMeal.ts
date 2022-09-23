import useApi from "@/hooks/api";
import { ref } from "vue";

export type LeaveMeal = {
    slotId: number
};

export async function useLeaveMeal(data: string) {
    const { error, request, response } = useApi<LeaveMeal>(
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

    return {error, response}
}