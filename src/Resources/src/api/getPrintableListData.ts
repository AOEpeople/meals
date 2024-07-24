import useApi from '@/api/api';
import { onMounted, onUnmounted, reactive, readonly, ref } from 'vue';
import type { Dictionary } from '@/types/types';
import type { DateTime } from '@/api/getDashboardData';

export type ListData = {
    data: Dictionary<Dictionary<Dictionary<Array<number>>>>;
    meals: Dictionary<MealData>;
    day: DateTime;
};

type MealData = {
    title: {
        en: string;
        de: string;
    };
    parent?: number | null;
    participations?: number;
};

const listDataState = reactive<ListData>({
    data: {},
    meals: {},
    day: {
        date: '',
        timezone_type: 0,
        timezone: ''
    }
});
/**
 * if date is passed participants list is specific to that date, if not it returns the list of today's participants
 * @param date
 * @returns list of participants
 */
export function usePrintableListData(date?: string) {
    const loaded = ref(false);

    onMounted(async () => {
        await getListData();
    });

    onUnmounted(() => {
        listDataState.data = {};
    });

    async function getListData() {
        const {
            response: listData,
            request,
            error
        } = useApi<ListData>(
            'GET',
            date !== undefined && date !== null ? `/api/print/participations/${date}` : '/api/print/participations/'
        );

        if (loaded.value === false) {
            await request();
            loaded.value = true;

            if (error.value === false && listData.value !== null && listData.value !== undefined) {
                listDataState.data = listData.value.data;
                listDataState.meals = listData.value.meals;
                listDataState.day = listData.value.day;
            }
        }
    }

    return {
        listData: readonly(listDataState)
    };
}
