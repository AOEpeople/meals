import useApi from "@/hooks/api";
import { ref } from "vue";

export async function useCancelOffer(data: string) {
    const { request, response, error } = useApi(
        "POST",
        "api/cancel-offer",
        'application/json',
        data,
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { response, error }
}