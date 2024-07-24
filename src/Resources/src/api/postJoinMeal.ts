import useApi from '@/api/api';
import { type IMessage } from '@/interfaces/IMessage';
import { ref } from 'vue';

export type JoinMeal = {
    slotId: number;
    participantId: number;
};

export async function useJoinMeal(data: string) {
    const { request, response, error } = useApi<JoinMeal | IMessage>(
        'POST',
        'api/meal/participation',
        'application/json',
        data
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { response, error };
}
