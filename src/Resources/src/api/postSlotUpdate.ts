import useApi from "@/api/api";
import { ref } from "vue";
import { TimeSlot } from "@/stores/timeSlotStore";

/**
 * Performs a POST request to update a slot
 * @param data Stringified data cointaining the id of the slot and the data to be changed
 * @returns The updated slot
 */
async function postUpdateSlot(data: string) {
    const { error, request, response } = useApi<TimeSlot>(
        'POST',
        'api/slot/update',
        'application/json',
        data,
    );

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { error, response }
}

export function useUpdateSlot() {

    /**
     * Calls postSlotUpdate to enable or disable a slot
     * @param id ID of the slot to be changed
     * @param state current enabled state of the slot
     */
    async function updateSlotEnabled(id: number, state: boolean) {
        return postUpdateSlot(JSON.stringify({ id: id, enabled: state }));
    }

    /**
     * Calls postSlotUpdate to change the attributes of a slot
     * @param id ID of the slot to be changed
     * @param slot The slot as it should look after updating
     */
    async function updateTimeSlot(id: number, slot: TimeSlot) {
        return postUpdateSlot(JSON.stringify({ id: id, title: slot.title, limit: slot.limit, order: slot.order }))
    }

    return {
        updateSlotEnabled,
        updateTimeSlot
    }
}