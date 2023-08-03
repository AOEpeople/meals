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

    // const addedProfiles = ref<IProfile[]>([]);

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

    // potentially still to be used
    // function removeProfileFromState(searchProfile: IProfile) {
    //     const indexToDelete = ProfilesState.profiles.findIndex(profile => searchProfile.user === profile.user);
    //     addedProfiles.value.push(...ProfilesState.profiles.splice(indexToDelete, 1));
    // }

    // function putAddedProfilesBackInState() {
    //     ProfilesState.profiles = [...ProfilesState.profiles, ...addedProfiles.value];
    // }

    return {
        ProfilesState: readonly(ProfilesState),
        fetchAbsentingProfiles
    };
}