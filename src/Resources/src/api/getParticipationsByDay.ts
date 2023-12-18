import useApi from '@/api/api';
import { onMounted, onUnmounted, readonly, ref } from 'vue';

export type ListData = []

const listDataState = ref([])

/**
 * if date is passed participants list is specific to that date, if not it returns the list of today's participants
 * @param date
 * @returns list of participants
 */
export function useParticipationsListData(date: string){

    const loaded = ref(false)

    onMounted(async () => {
        await getListData();
    });

    onUnmounted(() => {
        listDataState.value = [];
    });

    async function getListData() {
        const { response: listData, request, error } = useApi<ListData>(
            'GET',
            `/api/participations/day/${date}`
        );

        console.log("Meals huraaaaaaaaaa!!!!!!! 8477987r987r97" + date);
        if (loaded.value === false) {
            await request();
            loaded.value = true;

            listDataState.value = listData.value;
        }
    }

    return {
        listData: readonly(listDataState)
    }
}

