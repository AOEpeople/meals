import { IBookedData } from "@/api/getShowParticipations";
import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";
import getParticipations from "@/api/getParticipations";
import { isResponseDictOkay } from "@/api/isResponseOkay";

interface IMenuParticipationsState {
    days: Dictionary<Dictionary<IBookedData>>,
    error: string,
    isLoading: boolean
}

// TODO: to be implemented
function isDaysDict(days: Dictionary<Dictionary<IBookedData>>) {
    return null;
}

const menuParticipationsState = reactive<IMenuParticipationsState>({
    days: {},
    error: '',
    isLoading: false
});

export function useParticipations(weekId: number) {

    async function fetchParticipations() {
        menuParticipationsState.isLoading = true;

        const { error, participations } = await getParticipations(weekId);

        if (isResponseDictOkay(error, participations) === true) {
            menuParticipationsState.days = participations.value;
            menuParticipationsState.error = '';
        } else {
            menuParticipationsState.error = 'Error on getting the participations';
        }

        menuParticipationsState.isLoading = false;
    }

    function getParticipants() {
        const participants = new Set<string>();

        for(const day of Object.values(menuParticipationsState.days)) {
            Object.keys(day).forEach((participant) => participants.add(participant));
        }

        return participants;
    }

    function countBookedMeal(dayId: string, dishId: number) {
        const day = menuParticipationsState.days[dayId];

        let count = 0;
        for(const participant of Object.values(day)) {
            if (participant.booked.includes(dishId)) {
                count++;
            }
        }
        return count;
    }

    return {
        menuParticipationsState: readonly(menuParticipationsState),
        fetchParticipations,
        getParticipants,
        countBookedMeal
    }
}