import {Store} from "@/store/store";
import {TimeSlots, useTimeSlotData} from "@/hooks/getTimeSlotData";
import {useUpdateSlot} from "@/hooks/postSlotUpdate";
import {useUpdateSelectedSlot} from "@/hooks/postUpdateSelectedSlot";

type TimeSlot = {
    slots: TimeSlots,
    isLoading: boolean
}

class TimeSlotStore extends Store<TimeSlot> {
    protected data(): TimeSlot {
        return {
            slots: {},
            isLoading: true
        };
    }

    public async fillStore() {
        this.state.isLoading = true;
        let {timeslots} = await useTimeSlotData();
        if (timeslots.value) {
            this.state.slots = timeslots.value;
            this.state.isLoading = false;
        } else {
            console.log('could not receive TimeSlots');
        }
    }

    public async changeDisabledState(id: number, state: boolean): Promise<boolean> {
        let data = {
            id: id,
            enabled: state
        }

        const {error} = await useUpdateSlot(JSON.stringify(data))

        if (error.value === false) {
            this.state.slots[id].enabled = state;
            return true
        }
        return false
    }
}

export const timeSlotStore: TimeSlotStore = new TimeSlotStore()