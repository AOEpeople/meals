import useApi from "@/hooks/api";
import { ref } from "vue";

export default async function useTransactionForm(){
    const { response: transactionForm, request } = useApi(
        "GET",
        "payment/ecash/form/" + sessionStorage.getItem('user'),
        "text/html",
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { transactionForm };
}