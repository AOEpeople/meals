import useApi from "@/api/api";
import { ref } from "vue"
import type {Dictionary} from "../../types/types";
import type {DateTime} from "@/api/getDashboardData"

type ListData = {
    data: Dictionary<Dictionary<boolean>>
    meals: {
        en: string,
        de: string
    },
    day: DateTime,
    participations: Dictionary<number>
}

export async function usePrintableListData(){
    const { response: listData, request, error } = useApi<ListData>(
        "GET",
        "/api/print/participations",
    );

    const loaded = ref(false)

    if (loaded.value === false) {
        await request()
        loaded.value = true
    }

    return { listData, error }
}