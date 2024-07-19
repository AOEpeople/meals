import { ref } from 'vue';
import useApi from './api';

export async function getIsParticipating(mealId: number) {
    const { response, error, request } = useApi<number>('GET', `/api/participation/${mealId}`);

    await request();

    const participationId = response.value !== -1 ? response : ref(null);

    return { response: participationId, error };
}
