import useApi from '@/api/api';
import { ref } from 'vue';
import { type TimeSlot } from '@/stores/timeSlotStore';

/**
 * Performs a PUT request to update a slot
 * @param slug The identifier of the slot to be changed
 * @param data Stringified data cointaining the id of the slot and the data to be changed
 * @returns The updated slot
 */
async function putUpdateSlot(slug: string, data: string) {
    const { error, request, response } = useApi<TimeSlot>('PUT', `api/slots/${slug}`, 'application/json', data);

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { error, response };
}

export function useUpdateSlot() {
    /**
     * Calls postSlotUpdate to enable or disable a slot
     * @param slug identifier of the slot to be changed
     * @param state current enabled state of the slot
     */
    async function updateSlotEnabled(slug: string, state: boolean) {
        return putUpdateSlot(slug, JSON.stringify({ enabled: state }));
    }

    /**
     * Calls postSlotUpdate to change the attributes of a slot
     * @param slug idetifier of the slot to be changed
     * @param slot The slot as it should look after updating
     */
    async function updateTimeSlot(slot: TimeSlot) {
        return putUpdateSlot(slot.slug, JSON.stringify({ title: slot.title, limit: slot.limit, order: slot.order }));
    }

    return {
        updateSlotEnabled,
        updateTimeSlot
    };
}
