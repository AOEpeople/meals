import useApi from '@/api/api';
import type { DateTime } from '@/api/getDashboardData';
import { onMounted, onUnmounted, reactive, readonly, ref } from 'vue';
import type { Dictionary } from '../../types/types';

export type ListData = {
    data: Dictionary<Dictionary<Dictionary<Array<number>>>>
    day: DateTime
}


const listDataState = reactive<ListData>({
    data: {},
    day: {
        date: '',
        timezone_type: 0,
        timezone: ''
    }
});

export function useParticipantsByDayData(date: string){
    const loaded = ref(false)

    onMounted(async () => {
        await getListData();
    });

    onUnmounted(() => {
        listDataState.data = {};
    });

    async function getListData() {

        const { response: listData, request, error } = useApi<ListData>(
            'GET',
            `/api/print/participations/${date}`,
        );

        if (loaded.value === false) {
            await request();
            loaded.value = true;

            if (error.value === false && listData.value !== null && listData.value !== undefined) {
                listDataState.data = listData.value.data;
                listDataState.day = listData.value.day;
            }

        }
    }

    return {
        listData: readonly(listDataState)
    }
}