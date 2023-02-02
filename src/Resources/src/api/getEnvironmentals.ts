import useApi from "@/api/api";
import { ref } from "vue"

export type Env = {
    paypalId: string,
    mercureUrl: string
}

export async function useEnvs() {
    const { response: environmental, request, error } = useApi<Env>(
        "GET",
        "/api/environmentals",
    );

    const loaded = ref(false)

    if (loaded.value === false) {
        await request()
        loaded.value = true
    }

    console.log(environmental.value, request, error)
    if (!error.value) {
        sessionStorage.setItem('ENV', JSON.stringify(environmental.value));
    }

    return { env: environmental.value, error }
}