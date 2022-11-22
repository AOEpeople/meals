import useApi from "@/hooks/api"
import { ref } from "vue"

export async function usePaypal() {
    const paypalId = sessionStorage.getItem('paypalId')
    if (paypalId !== null) {
        return { id: paypalId }
    }

    const { response: id, request, error } = useApi<String>(
        "GET",
        "/api/paypal-id",
    );

    const loaded = ref(false)

    if (loaded.value === false) {
        await request()
        loaded.value = true
    }

    if (!error.value && typeof id.value === "string") {
        sessionStorage.setItem('paypalId', id.value)
    }

    return { id: id.value, error }
}