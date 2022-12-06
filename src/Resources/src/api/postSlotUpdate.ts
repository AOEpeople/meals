import useApi from "@/api/api";
import { ref } from "vue";

export async function useUpdateSlot(data: string) {
    const { error, request, response } = useApi(
        "POST",
        "api/update-slot",
        'application/json',
        data,
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return {error, response}
}