import useApi from '@/api/api';
import { onMounted, onUnmounted, readonly, ref } from 'vue';

export type ListData = string[];

const listDataState = ref<ListData>([]);

/**
 * if date is passed participants list is specific to that date, if not it returns the list of today's participants
 * @param date
 * @returns list of participants
 */
export function useParticipationsListData(date: string) {
    const loaded = ref(false);
    let useParticipationsError = false;

    onMounted(async () => {
        await getListData();
    });

    onUnmounted(() => {
        listDataState.value = [];
    });

    async function getListData() {
        if (date === undefined) {
            return;
        }

        const { error, response: listData, request } = useApi<ListData>('GET', `/api/participations/day/${date}`);
        useParticipationsError = error.value;

        if (loaded.value === false) {
            await request();
            loaded.value = true;

            listDataState.value = listData.value;
        }
    }
    return {
        useParticipationsError,
        listData: readonly(listDataState),
        getListData
    };
}
