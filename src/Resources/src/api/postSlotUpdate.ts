import useApi from "@/api/api";
import { ref } from "vue";
import { TimeSlot } from "@/stores/timeSlotStore";
import { stringify } from "querystring";

async function postUpdateSlot(data: string) {
    const { error, request, response } = useApi<TimeSlot>(
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

export function useUpdateSlot() {
    async function updateSlotEnabled(id: number, state: boolean) {
        return postUpdateSlot(JSON.stringify({ id: id, enabled: state }));
    }

    async function updateTimeSlot(id: number, slot: TimeSlot) {
        return postUpdateSlot(JSON.stringify({ id: id, title: slot.title, limit: slot.limit, order: slot.order }))
    }

    return {
        updateSlotEnabled,
        updateTimeSlot
    }
}