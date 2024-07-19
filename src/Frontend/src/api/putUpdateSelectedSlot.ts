import useApi from '@/api/api';
import { ref } from 'vue';

export async function useUpdateSelectedSlot(data: string) {
    const { request, response, error } = useApi('PUT', 'api/participation/slot', 'application/json', data);

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { response, error };
}
