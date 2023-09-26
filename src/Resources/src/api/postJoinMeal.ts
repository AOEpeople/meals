import useApi from '@/api/api';
import { ref } from 'vue';

export type JoinMeal = {
    slotId: number,
    participantId: number,
    mealState: string,
};

export async function useJoinMeal(data: string) {
    const { request, response, error } = useApi<JoinMeal>(
        'POST',
        'api/join-meal',
        'application/json',
        data,
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { response, error }
}