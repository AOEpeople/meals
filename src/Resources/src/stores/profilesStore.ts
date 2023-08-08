import getAbsentingProfiles from "@/api/getAbsentingProfiles";
import { isResponseArrayOkay } from "@/api/isResponseOkay";
import { reactive, readonly } from "vue";

interface IProfilesState {
    profiles: IProfile[],
    error: string,
    isLoading: boolean
}

export interface IProfile {
    user: string,
    fullName: string,
    roles: string[]
}

/**
 * Checks if the given object is of type IProfile.
 * @param profile The profile to check.
 */
function isProfile(profile: IProfile): profile is IProfile {
    return (
        profile !== null &&
        profile !== undefined &&
        typeof (profile as IProfile)?.user === 'string' &&
        (profile as IProfile)?.roles !== null &&
        (profile as IProfile)?.roles !== undefined &&
        Object.keys(profile).length === 3
    );
}

export function useProfiles(weekId: number) {

    const ProfilesState = reactive<IProfilesState>({
        profiles: [],
        error: '',
        isLoading: false
    });

    /**
     * Fetches the absenting profiles for the weekId given by usePofiles() and stores them in the ProfilesState.
     */
    async function fetchAbsentingProfiles() {
        ProfilesState.isLoading = true;
        const { error, response } = await getAbsentingProfiles(weekId);

        if (isResponseArrayOkay<IProfile>(error, response, isProfile) === true) {
            ProfilesState.profiles = response.value;
            ProfilesState.error = '';
        } else {
            ProfilesState.error = 'Error on fetching absenting Profiles';
        }
        ProfilesState.isLoading = false;
    }

    return {
        ProfilesState: readonly(ProfilesState),
        fetchAbsentingProfiles
    };
}