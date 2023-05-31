import useApi from "@/api/api";
import { onMounted, reactive, readonly, ref } from "vue"
import type {Dictionary} from "../../types/types";
import type {DateTime} from "@/api/getDashboardData"

export type ListData = {
    data: Dictionary<Dictionary<Dictionary<Array<number>>>>
    meals: Dictionary<MealData>,
    day: DateTime
}

type MealData = {
    title: {
        en: string,
        de: string
    },
    parent?: number | null,
    participations?: number
}

const listDataState = reactive<ListData>({
    data: {},
    meals: {},
    day: {
        date: '',
        timezone_type: 0,
        timezone: ''
    }
});

export function usePrintableListData(){

    const loaded = ref(false)

    onMounted(async () => {
        await getListData();
    });

    async function getListData() {
        const { response: listData, request, error } = useApi<ListData>(
            'GET',
            '/api/print/participations',
        );

        if (loaded.value === false) {
            await request();
            loaded.value = true;

            if(!error.value && listData.value) {
                listDataState.data = listData.value.data;
                listDataState.meals = listData.value.meals;
                listDataState.day = listData.value.day;
            }
        }
    }

    return {
        listData: readonly(listDataState)
    }
}