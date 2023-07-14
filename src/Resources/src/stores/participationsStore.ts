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

export function useParticipations(weekId: number) {

    const menuParticipationsState = reactive<IMenuParticipationsState>({
        days: {},
        error: '',
        isLoading: false
    });

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

    return {
        menuParticipationsState: readonly(menuParticipationsState),
        fetchParticipations
    }
}