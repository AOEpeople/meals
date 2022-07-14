import useApi from "@/hooks/api"
import { ref } from "vue"

export type Transaction = {
    data: [{
        type: string,
        date: Object,
        timestamp: string,
        description_en: string,
        description_de: string,
        amount: number,
    }],
    difference: number,
}

export async function useTransactionData(){
    const { response: transactions, request } = useApi<Transaction>(
        "GET",
        "api/transactions",
    );

    const loaded = ref(false)

    if (loaded.value === false) {
        await request()
        loaded.value = true
    }

    return { transactions }
}