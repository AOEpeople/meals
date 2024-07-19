import useApi from '@/api/api';
import { ref } from 'vue';

export type LeaveMeal = {
    slotId: number;
};

export async function useLeaveMeal(data: string) {
    const { error, request, response } = useApi<LeaveMeal>(
        'DELETE',
        'api/meal/participation',
        'application/json',
        data
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { error, response };
}
