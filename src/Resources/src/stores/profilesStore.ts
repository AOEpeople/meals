import { reactive, readonly } from "vue";

interface IProfilesState {
    profiles: IProfile[],
    error: boolean,
    isLoading: boolean
}

interface IProfile {
    user: string,
    roles: string[]
}

export function useProfiles() {

    const ProfilesState = reactive<IProfilesState>({
        profiles: [],
        error: false,
        isLoading: false
    });

    return {
        ProfilesState: readonly(ProfilesState)
    };
}