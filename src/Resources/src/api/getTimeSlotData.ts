import useApi from "@/api/api";
import { ref } from "vue"

type TimeSlot = {
    title: string,
    limit: number,
    order: number,
    enabled: boolean
}

export type TimeSlots = {
    [id: number]: TimeSlot
}

export async function useTimeSlotData(){
    const { response: timeslots, request, error } = useApi<TimeSlots>(
        "GET",
        "api/timeslots",
    );

    const loaded = ref(false)

    if (loaded.value === false) {
        await request()
        loaded.value = true
    }

    return { timeslots, error }
}