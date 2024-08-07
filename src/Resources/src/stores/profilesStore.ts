import getAbsentingProfiles from '@/api/getAbsentingProfiles';
import { isResponseArrayOkay, isResponseObjectOkay } from '@/api/isResponseOkay';
import { reactive, readonly, watch, type Ref } from 'vue';
import getProfileWithHash from '@/api/getProfileWithHash';
import { type IMessage, isMessage } from '@/interfaces/IMessage';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import { useI18n } from 'vue-i18n';

interface IProfilesState {
    profiles: IProfile[];
    error: string;
    isLoading: boolean;
}

export interface IProfile {
    user: string;
    fullName: string;
    roles: string[];
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

    const { sendFlashMessage } = useFlashMessage();
    const { t } = useI18n();

    watch(
        () => ProfilesState.error,
        () => {
            if (ProfilesState.error !== '') {
                sendFlashMessage({
                    type: FlashMessageType.ERROR,
                    message: ProfilesState.error
                });
            }
        }
    );

    /**
     * Fetches the absenting profiles for the weekId given by usePofiles() and stores them in the ProfilesState.
     */
    async function fetchAbsentingProfiles() {
        ProfilesState.isLoading = true;
        const { error, response } = await getAbsentingProfiles(weekId);

        if (isResponseArrayOkay<IProfile>(error, response, isProfile) === true) {
            ProfilesState.profiles = response.value as IProfile[];
            ProfilesState.error = '';
        } else {
            ProfilesState.error = 'Error on fetching absenting Profiles';
        }
        ProfilesState.isLoading = false;
    }

    /**
     * Fetches the profile for a given settlement hash.
     * @param hash  The hash to fetch the profile for.
     */
    async function fetchProfileWithHash(hash: string): Promise<IProfile | null> {
        const { error, profile } = await getProfileWithHash(hash);

        if (error.value === true && isMessage(profile) === true) {
            ProfilesState.error = (profile.value as IMessage).message;
        } else if (isResponseObjectOkay(error, profile as Ref<IProfile>, isProfile)) {
            return profile.value as IProfile;
        }

        return null;
    }

    /**
     * Creates a localized string with the name of the profile.
     * @param profile   The profile
     * @returns         The localized string
     */
    function getDisplayName(profile: IProfile) {
        if (profile.roles.includes('ROLE_GUEST')) {
            return `(${t('menu.guest')}) ${profile.fullName}`;
        }
        return profile.fullName;
    }

    return {
        ProfilesState: readonly(ProfilesState),
        fetchAbsentingProfiles,
        fetchProfileWithHash,
        getDisplayName
    };
}
