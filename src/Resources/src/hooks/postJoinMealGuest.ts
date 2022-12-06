import useApi from '@/hooks/api'
import { ref } from 'vue'

export async function useJoinMealGuest(data: string) {
    const { request, response, error } = useApi(
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

    return { response, error }
}