import useApi from "@/hooks/api"
import { ref } from "vue"
import type {Dictionary} from "../../types/types";
import type {DateTime} from "@/hooks/getDashboardData"

type ListData = {
    data: Dictionary<Dictionary<boolean>>
    meals: {
        en: String,
        de: String
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