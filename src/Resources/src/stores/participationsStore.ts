import { Dictionary } from "types/types";
import { Ref, reactive, readonly } from "vue";
import getParticipations from "@/api/getParticipations";
import { isResponseDictOkay, isResponseObjectOkay } from "@/api/isResponseOkay";
import putParticipation from "@/api/putParticipation";
import { isMessage, IMessage } from "@/interfaces/IMessage";
import deleteParticipation from "@/api/deleteParticipation";
import { profile } from "console";

interface IMenuParticipationsState {
    days: IMenuParticipationDays,
    error: string,
    isLoading: boolean
}

export type IMenuParticipationDays = {
    [dayId: string]: IMenuParticipant
}

type IMenuParticipant = {
    [participantName: string]: IMenuParticipation
}
export interface IMenuParticipation {
    booked: Dictionary<IMealInfo>,
    profile: string
}

interface IMealInfo {
    mealId: number,
    dishId: number,
    combinedDishes: number[]
}

export interface IParticipationUpdate extends IMenuParticipation {
    day: number
}

// TODO: to be implemented
function isDaysDict(days: IMenuParticipationDays) {
    return null;
}

function isParticipationUpdate(participationUpdate: IParticipationUpdate): participationUpdate is IParticipationUpdate {
    return (
        participationUpdate !== null &&
        participationUpdate !== undefined &&
        typeof (participationUpdate as IParticipationUpdate).day === 'number' &&
        typeof (participationUpdate as IParticipationUpdate).profile === 'string' &&
        Object.keys(participationUpdate).length === 3
    );
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

    async function addParticipantToMeal(mealId: number, profileFullname: string, dayId: string, combinedDishes?: string[]) {

        const profileId = getProfileId(profileFullname);
        if (typeof profileId !== 'string') {
            return;
        }

        const { error, response } = await putParticipation(mealId, profileId, combinedDishes);

        handleParticipationUpdate(response, error, dayId, profileFullname);
    }

    async function removeParticipantFromMeal(mealId: number, profileFullname: string, dayId: string) {

        const profileId = getProfileId(profileFullname);
        if (typeof profileId !== 'string') {
            return;
        }

        const { error, response } = await deleteParticipation(mealId, profileId);

        handleParticipationUpdate(response, error, dayId, profileFullname);
    }

    function handleParticipationUpdate(response: Ref<IMessage | IParticipationUpdate>, error: Ref<boolean>, dayId: string, profileFullname: string) {
        if (isMessage(response.value) === false && isResponseObjectOkay<IParticipationUpdate>(error, (response as Ref<IParticipationUpdate>), isParticipationUpdate)) {
            menuParticipationsState.error = '';
            if (menuParticipationsState.days[dayId][profileFullname] !== undefined) {
                menuParticipationsState.days[dayId][profileFullname].booked = (response.value as IMenuParticipation).booked;
            } else {
                menuParticipationsState.days[dayId][profileFullname] = {
                    booked: (response.value as IMenuParticipation).booked,
                    profile: (response.value as IMenuParticipation).profile
                };
            }
        } else if (isMessage(response.value) === true) {
            menuParticipationsState.error = (response.value as IMessage).message;
        } else {
            menuParticipationsState.error = 'Unknown Error occured on updating participations';
        }
    }

    function getParticipants() {
        const participants = new Set<string>();

        for (const day of Object.values(menuParticipationsState.days)) {
            Object.keys(day).forEach((participant) => participants.add(participant));
        }

        return [...participants].sort();
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
            if (Object.values(participant.booked).find(mealInfo => mealInfo.dishId === dishId) !== undefined) {
                count++;
            }
        }
        return count;
    }

    function hasParticipantBookedMeal(dayId: string, participant: string, mealId: number) {
        const participantMeals = menuParticipationsState.days[dayId][participant]?.booked;
        if (participantMeals !== null && participantMeals !== undefined) {
            return Object.values(participantMeals).find(mealInfo => mealInfo.mealId === mealId) !== undefined;
        }
        return false;
    }

    function hasParticipantBookedCombiDish(dayId: string, participant: string, dishId: number) {
        const participantMeals = menuParticipationsState.days[dayId][participant]?.booked;

        if (participantMeals !== null && participantMeals !== undefined) {
            for (const meal of Object.values(participantMeals)) {
                if (meal.combinedDishes.length > 0 && meal.combinedDishes.includes(dishId)) {
                    return true;
                }
            }
        }

        return false;
    }

    return {
        menuParticipationsState: readonly(menuParticipationsState),
        fetchParticipations,
        getParticipants,
        countBookedMeal,
        getProfileId,
        addParticipantToMeal,
        removeParticipantFromMeal,
        hasParticipantBookedMeal,
        hasParticipantBookedCombiDish
    }
}