import type { Dictionary } from '@/types/types';
import { type Ref, reactive, readonly, watch } from 'vue';
import getParticipations from '@/api/getParticipations';
import { isResponseObjectOkay } from '@/api/isResponseOkay';
import putParticipation from '@/api/putParticipation';
import { isMessage, type IMessage } from '@/interfaces/IMessage';
import deleteParticipation from '@/api/deleteParticipation';
import type { IProfile } from './profilesStore';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import replaceStrings from '@/tools/stringReplacer';

interface IMenuParticipationsState {
    days: IMenuParticipationDays;
    error: string;
    isLoading: boolean;
    filterStr: string;
}

export type IMenuParticipationDays = {
    [dayId: string]: IMenuParticipant;
};

type IMenuParticipant = {
    [participantName: string]: IMenuParticipation;
};
export interface IMenuParticipation {
    booked: Dictionary<IMealInfo>;
    profile: string;
}

interface IMealInfo {
    mealId: number;
    dishId: number;
    combinedDishes: number[];
}

export interface IParticipationUpdate extends IMenuParticipation {
    day: number;
}

/**
 * Checks if the given object is of type IMenuParticipationDays.
 * Effectivly only checks if the object is not null, undefined or empty.
 * @param days The object to check.
 */
function isMenuParticipation(days: IMenuParticipationDays): days is IMenuParticipationDays {
    return days !== null && days !== undefined && Object.keys(days).length > 0;
}

/**
 * Checks if the given object is of type IParticipationUpdate.
 * @param participationUpdate The object to check.
 */
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
    isLoading: false,
    filterStr: ''
});

const { sendFlashMessage } = useFlashMessage();

watch(
    () => menuParticipationsState.error,
    () => {
        if (menuParticipationsState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: menuParticipationsState.error
            });
        }
    }
);

export function useParticipations(weekId: number) {
    /**
     * Fetches the participations of a week that is given by useParticipations
     * and stores them in the state if the response is okay.
     */
    async function fetchParticipations() {
        menuParticipationsState.isLoading = true;

        const { error, participations } = await getParticipations(weekId);

        if (isResponseObjectOkay<IMenuParticipationDays>(error, participations, isMenuParticipation) === true) {
            menuParticipationsState.days = participations.value as IMenuParticipationDays;
            menuParticipationsState.error = '';
        } else {
            menuParticipationsState.error = 'Error on getting the participations';
        }

        menuParticipationsState.isLoading = false;
    }

    /**
     * Performs a put request to add a participant to a meal. The response is handled by handleParticipationUpdate().
     * @param mealId            The id of the meal to add the participant to.
     * @param profileFullname   The full name of the participant to add.
     * @param dayId             The id of the day the meal is on.
     * @param combinedDishes    The ids of the dishes that make up the combined dish. Only needed if the dish is a combined dish.
     */
    async function addParticipantToMeal(
        mealId: number,
        profileFullname: string,
        dayId: string,
        combinedDishes?: string[]
    ) {
        const profileId = getProfileId(profileFullname);
        if (typeof profileId !== 'string') {
            return;
        }

        menuParticipationsState.error = '';
        const { error, response } = await putParticipation(mealId, profileId, combinedDishes);

        handleParticipationUpdate(response, error, dayId, profileFullname);
        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'participations.added'
        });
    }

    /**
     * Performs a delete request to remove a participant from a meal. The response is handled by handleParticipationUpdate().
     * @param mealId            The id of the meal to remove the participant from.
     * @param profileFullname   The full name of the participant to remove.
     * @param dayId             The id of the day the meal is on.
     */
    async function removeParticipantFromMeal(mealId: number, profileFullname: string, dayId: string) {
        const profileId = getProfileId(profileFullname);
        if (typeof profileId !== 'string') {
            return;
        }

        const { error, response } = await deleteParticipation(mealId, profileId);

        handleParticipationUpdate(response, error, dayId, profileFullname);
        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'participations.removed'
        });
    }

    /**
     * Proccesses the response of removing or adding a participant to a meal.
     * It updates the participation state on a single day for a single participant.
     * @param response          The response of the request.
     * @param error             The error of the request. True if there is an error.
     * @param dayId             The id of the day the meal is on.
     * @param profileFullname   The full name of the participant (is equivalent to the key in the dict).
     */
    function handleParticipationUpdate(
        response: Ref<IMessage | IParticipationUpdate | undefined>,
        error: Ref<boolean>,
        dayId: string,
        profileFullname: string
    ) {
        const formattedName = replaceStrings(profileFullname, ' (Guest)', ' (Gast)');
        if (
            isMessage(response.value) === false &&
            isResponseObjectOkay<IParticipationUpdate>(
                error,
                response as Ref<IParticipationUpdate>,
                isParticipationUpdate
            )
        ) {
            menuParticipationsState.error = '';
            if (menuParticipationsState.days[dayId][formattedName] !== undefined) {
                menuParticipationsState.days[dayId][formattedName].booked = (
                    response.value as IMenuParticipation
                ).booked;
            } else {
                menuParticipationsState.days[dayId][formattedName] = {
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

    /**
     * Adds an empty participation to the state for a given profile.
     * Used to prepare adding an abstaining profile to the participations.
     * @param profile The profile to add an empty participation for.
     */
    function addEmptyParticipationToState(profile: IProfile) {
        const firstDayId = Object.keys(menuParticipationsState.days)[0];
        menuParticipationsState.days[firstDayId][profile.fullName] = {
            booked: {},
            profile: profile.user
        };
    }

    /**
     * Returns a unique and sorted list of full names of all participants in the current week.
     */
    function getParticipants() {
        const participants = new Set<string>();

        for (const day of Object.values(menuParticipationsState.days)) {
            Object.keys(day).forEach((participant) => participants.add(participant));
        }

        return [...participants].sort();
    }

    /**
     * Returns the profile id of a participant.
     * @param participant   The full name of the participant.
     */
    function getProfileId(participant: string) {
        const strippedParticipant = replaceStrings(participant, ' (Guest)', ' (Gast)');
        for (const day of Object.values(menuParticipationsState.days)) {
            if (typeof day[strippedParticipant]?.profile === 'string') {
                return day[strippedParticipant].profile;
            }
        }
        return null;
    }

    /**
     * Returns how often a dish is booked on a given day.
     * @param dayId     The id of the day.
     * @param dishId    The id of the dish.
     */
    function countBookedMeal(dayId: string, dishId: number) {
        if (dishId < 0) return 0;
        const day = menuParticipationsState.days[dayId];

        let count = 0;
        for (const participant of Object.values(day)) {
            if (Object.values(participant.booked).find((mealInfo) => mealInfo.dishId === dishId) !== undefined) {
                count++;
            }
        }
        return count;
    }

    /**
     * Checks if a participant has booked a meal on a specific day.
     * @param dayId         The id of the day.
     * @param participant   The full name of the participant.
     * @param mealId        The id of the meal.
     */
    function hasParticipantBookedMeal(dayId: string, participant: string, mealId: number) {
        const participantMeals = menuParticipationsState.days[dayId][participant]?.booked;
        if (participantMeals !== null && participantMeals !== undefined) {
            return Object.values(participantMeals).find((mealInfo) => mealInfo.mealId === mealId) !== undefined;
        }
        return false;
    }

    /**
     * Checks if a participant has booked a combined meal containing a specific dish on a given day.
     * @param dayId         The id of the day.
     * @param participant   The full name of the participant.
     * @param dishId        The id of the dish.
     */
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

    /**
     * Sets the filter string for the participations.
     * @param str  The filter string.
     */
    function setFilter(str: string) {
        menuParticipationsState.filterStr = str;
    }

    /**
     * Returns the filter string for the participations.
     */
    function getFilter() {
        return menuParticipationsState.filterStr;
    }

    /**
     * Resets the state to the initial state, only to be used in testing
     */
    function resetStates() {
        menuParticipationsState.days = {};
        menuParticipationsState.error = '';
        menuParticipationsState.isLoading = false;
        menuParticipationsState.filterStr = '';
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
        hasParticipantBookedCombiDish,
        addEmptyParticipationToState,
        setFilter,
        getFilter,
        resetStates
    };
}
