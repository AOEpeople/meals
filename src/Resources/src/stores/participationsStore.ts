import { IBookedData } from "@/api/getShowParticipations";
import { Dictionary } from "types/types";
import { Ref, reactive, readonly, watch } from "vue";
import getParticipations from "@/api/getParticipations";
import { isResponseDictOkay, isResponseObjectOkay } from "@/api/isResponseOkay";
import putParticipation from "@/api/putParticipation";
import { isMessage, IMessage } from "@/interfaces/IMessage";
import deleteParticipation from "@/api/deleteParticipation";

interface IMenuParticipationsState {
    days: Dictionary<Dictionary<IMenuParticipation>>,
    error: string,
    isLoading: boolean
}

export interface IMenuParticipation extends IBookedData {
    booked: number[],
    profile: string
}

export interface IParticipationUpdate {
    day: number,
    profile: string,
    bookedDishes: number[]
}

// TODO: to be implemented
function isDaysDict(days: Dictionary<Dictionary<IMenuParticipation>>) {
    return null;
}

function isParticipationUpdate(participationUpdate: IParticipationUpdate) {
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

    async function addParticipantToMeal(mealId: number, profileFullname: string, dayId: string) {

        const profileId = getProfileId(profileFullname);
        if (typeof profileId !== 'string') {
            return;
        }

        const { error, response } = await putParticipation(mealId, profileId);

        if (isMessage(response.value) === false && isResponseObjectOkay<IParticipationUpdate>(error, (response as Ref<IParticipationUpdate>))) {
            if (
                parseInt(dayId) !== (response.value as IParticipationUpdate).day ||
                profileFullname !== (response.value as IParticipationUpdate).profile
            ) {
                menuParticipationsState.error = 'Unknown Error occured on updating participations';
                return;
            }

            menuParticipationsState.error = '';
            menuParticipationsState.days[dayId][profileFullname].booked = (response.value as IParticipationUpdate).bookedDishes;
        } else if (isMessage(response.value) === true) {
            menuParticipationsState.error = (response.value as IMessage).message;
        } else {
            menuParticipationsState.error = 'Unknown Error occured on updating participations';
        }
    }

    async function removeParticipantFromMeal(mealId: number, profileFullname: string, dayId: string) {

        const profileId = getProfileId(profileFullname);
        if (typeof profileId !== 'string') {
            return;
        }

        const { error, response } = await deleteParticipation(mealId, profileId);

        if (isMessage(response.value) === false && isResponseObjectOkay<IParticipationUpdate>(error, (response as Ref<IParticipationUpdate>))) {
            if (
                parseInt(dayId) !== (response.value as IParticipationUpdate).day ||
                profileFullname !== (response.value as IParticipationUpdate).profile
            ) {
                menuParticipationsState.error = 'Unknown Error occured on deleting participations';
                return;
            }

            menuParticipationsState.error = '';
            menuParticipationsState.days[dayId][profileFullname].booked = (response.value as IParticipationUpdate).bookedDishes;
        } else if (isMessage(response.value) === true) {
            menuParticipationsState.error = (response.value as IMessage).message;
        } else {
            menuParticipationsState.error = 'Unknown Error occured on deleting participations';
        }
    }

    function getParticipants() {
        const participants = new Set<string>();

        for (const day of Object.values(menuParticipationsState.days)) {
            Object.keys(day).forEach((participant) => participants.add(participant));
        }

        return participants;
    }

    function getProfileId(participant: string) {
        for (const day of Object.values(menuParticipationsState.days)) {
            if (typeof day[participant]?.profile === 'string') {
                return day[participant].profile;
            }
        }
        return null;
    }

    function countBookedMeal(dayId: string, dishId: number) {
        const day = menuParticipationsState.days[dayId];

        let count = 0;
        for (const participant of Object.values(day)) {
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
        countBookedMeal,
        getProfileId,
        addParticipantToMeal,
        removeParticipantFromMeal
    }
}