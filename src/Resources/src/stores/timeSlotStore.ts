import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";
import { useTimeSlotData } from "@/api/getTimeSlotData";
import { useUpdateSlot } from "@/api/putSlotUpdate";
import postCreateSlot from "@/api/postCreateSlot";
import deleteSlot from "@/api/deleteSlot";
import { isMessage } from "@/interfaces/IMessage";
import { isResponseObjectOkay, isResponseDictOkay } from "@/api/isResponseOkay";

interface ITimeSlotState {
    timeSlots: Dictionary<TimeSlot>,
    isLoading: boolean,
    error: string
}

export interface TimeSlot {
    title: string,
    limit: number,
    order: number,
    enabled: boolean,
    slug?: string
}

export function isTimeSlot(timeSlot: TimeSlot): timeSlot is TimeSlot {
    return (
        typeof (timeSlot as TimeSlot).title === 'string' &&
        typeof (timeSlot as TimeSlot).limit === 'number' &&
        typeof (timeSlot as TimeSlot).order === 'number' &&
        typeof (timeSlot as TimeSlot).enabled === 'boolean' &&
        (Object.keys(timeSlot).length >= 4 && Object.keys(timeSlot).length <= 6)
    );
}

const TIMEOUT_PERIOD = 10000;

const TimeSlotState = reactive<ITimeSlotState>({
    timeSlots: {},
    isLoading: false,
    error: ''
});

export function useTimeSlots() {

    /**
     * Calls getTimeSlots to fetch timeSlots and sets isLoading
     * in the TimeSlotState to true during the request
     */
    async function fetchTimeSlots() {
        TimeSlotState.isLoading = true;
        await getTimeSlots();
        TimeSlotState.isLoading = false;
    }

    /**
     * Calls useTimeSlotData to fetch the timeSlots and sets the state accordingly.
     * Retries to fetch after a timeout if there are errors
     */
    async function getTimeSlots() {
        const { timeslots, error } = await useTimeSlotData();
        if (isResponseDictOkay<TimeSlot>(error, timeslots, isTimeSlot) === true) {
            TimeSlotState.timeSlots = timeslots.value;
            TimeSlotState.error = '';
        } else {
            setTimeout(fetchTimeSlots, TIMEOUT_PERIOD);
            TimeSlotState.error = 'Error on getting the TimeSlotData';
        }
    }

    /**
     * Enables or disables a slot
     * @param id The id of the slot
     * @param state The new state of the slot
     */
    async function changeDisabledState(id: number, state: boolean) {
        const { updateSlotEnabled } = useUpdateSlot();

        const { error, response } = await updateSlotEnabled(TimeSlotState.timeSlots[id].slug, state);

        if (isResponseObjectOkay<TimeSlot>(error, response, isTimeSlot) === true && response.value.enabled !== undefined) {
            updateTimeSlotEnabled(response.value, id);
        } else {
            TimeSlotState.error = 'Error on changing the slot state';
        }
    }

    /**
     * Updates a slot
     * @param id The id of the slot
     * @param slot The new slot data
     */
    async function editSlot(id: number, slot: TimeSlot) {
        const { updateTimeSlot } = useUpdateSlot();

        if(slot.slug === null) {
            slot.slug = TimeSlotState.timeSlots[id].slug;
        }

        const { error, response } = await updateTimeSlot(slot);

        if (isResponseObjectOkay<TimeSlot>(error, response, isTimeSlot) === true) {
            updateTimeSlotState(response.value, id);
        } else {
            TimeSlotState.error = 'Error on changing the slot state';
        }
    }

    /**
     * Updates the enabled state of a slot
     */
    function updateTimeSlotEnabled(newSlot: TimeSlot, id: number) {
        TimeSlotState.timeSlots[id].enabled = newSlot.enabled;
    }

    /**
     * Updates the data of a slot
     */
    function updateTimeSlotState(slot: TimeSlot, id: number) {
        TimeSlotState.timeSlots[id].title = slot.title;
        TimeSlotState.timeSlots[id].limit = slot.limit;
        TimeSlotState.timeSlots[id].order = slot.order;
        TimeSlotState.timeSlots[id].slug = slot.slug;
    }

    /**
     * Calls postCreateSlot to create a new slot
     * @param newSlot The new slot to be created
     */
    async function createSlot(newSlot: TimeSlot) {
        const { error, response } = await postCreateSlot(newSlot);

        if (error.value === true || isMessage(response.value) === true) {
            TimeSlotState.error = response.value?.message;
            return;
        }

        await getTimeSlots();
    }

    /**
     * Calls deleteSlot to delete a slot
     * @param slug The identifier of the slot to be deleted
     */
    async function deleteSlotWithSlug(slug: string) {
        const { error, response } = await deleteSlot(slug);

        if (error.value === true || isMessage(response.value) === true) {
            TimeSlotState.error = response.value?.message;
            return;
        }

        await getTimeSlots();
    }

    /**
     * Only to be used during testing
     */
    function resetState() {
        TimeSlotState.timeSlots = {};
        TimeSlotState.error = '';
        TimeSlotState.isLoading = false;
    }

    return {
        TimeSlotState: readonly(TimeSlotState),
        fetchTimeSlots,
        changeDisabledState,
        createSlot,
        deleteSlotWithSlug,
        editSlot,
        resetState
    }
}