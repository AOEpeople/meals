import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";
import { useTimeSlotData } from "@/api/getTimeSlotData";
import { useUpdateSlot } from "@/api/postSlotUpdate";
import postCreateSlot from "@/api/postCreateSlot";

interface ITimeSlotState {
    timeSlots: Dictionary<TimeSlot>,
    isLoading: boolean,
    error: string
}

export interface TimeSlot {
    title: string,
    limit: number,
    order: number,
    enabled: boolean
}

const TIMEOUT_PERIOD = 10000;

const TimeSlotState = reactive<ITimeSlotState>({
    timeSlots: {},
    isLoading: false,
    error: ""
});

export function useTimeSlots() {

    async function fetchTimeSlots() {
        TimeSlotState.isLoading = true;
        await getTimeSlots();
        TimeSlotState.isLoading = false;
    }

    async function getTimeSlots() {
        const { timeslots, error } = await useTimeSlotData();
        if(!error.value && timeslots.value) {
            TimeSlotState.timeSlots = timeslots.value;
            TimeSlotState.error = "";
        } else {
            setTimeout(fetchTimeSlots, TIMEOUT_PERIOD);
            TimeSlotState.error = "Error on getting the TimeSlotData";
        }
    }

    async function changeDisabledState(id: number, state: boolean) {
        const { updateSlotEnabled } = useUpdateSlot();

        const { error, response } = await updateSlotEnabled(id, state);

        if(!error.value && response.value && response.value.enabled !== undefined) {
            updateTimeSlotEnabled(response.value, id);
        } else {
            TimeSlotState.error = "Error on changing the slot state";
        }
    }

    function updateTimeSlotEnabled(newSlot: TimeSlot, id: number) {
        TimeSlotState.timeSlots[id].enabled = newSlot.enabled;
    }

    async function createSlot(newSlot: TimeSlot) {
        const { error, response } = await postCreateSlot(newSlot);

        if(error || response.value?.status !== "success") {
            TimeSlotState.error = "Error on creating slot";
            return;
        }

        await getTimeSlots();
    }

    return {
        TimeSlotState: readonly(TimeSlotState),
        fetchTimeSlots,
        changeDisabledState,
        createSlot
    }
}