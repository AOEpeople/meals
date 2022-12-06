import useApi from "@/api/api";
import { ref } from "vue"
import {DateTime, Meal, Slot} from "@/api/getDashboardData";
import {Dictionary} from "../../types/types";

type GuestDay = {
    date: DateTime
    meals: Dictionary<Meal>
    slots: Dictionary<Slot>
}

export async function useInvitationData(hash: string){
    const { response: invitation, request, error } = useApi<GuestDay>(
        "GET",
        "/api/guest-invitation-" + hash,
    );

    const loaded = ref(false)

    if (loaded.value === false) {
        await request()
        loaded.value = true
    }

    return { invitation, error }
}