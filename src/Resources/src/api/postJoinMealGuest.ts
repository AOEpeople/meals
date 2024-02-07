import useApi from '@/api/api';
import { ref } from 'vue';

export async function useJoinMealGuest(data: string) {
    const { request, response, error } = useApi('POST', 'api/guest/meal/participation', 'application/json', data);

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { response, error };
}
