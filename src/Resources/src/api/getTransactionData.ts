import useApi from "@/api/api";
import { ref } from "vue"

export type Transaction = {
    data: [{
        type: string,
        date: object,
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