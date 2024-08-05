import { useProfiles } from '@/stores/profilesStore';
import { ref } from 'vue';
import Profiles from '../fixtures/abstaining.json';
import HashedProfile from '../fixtures/hashProfile.json';
import { describe, it, expect, vi } from 'vitest';

const WEEK_ID = 123;

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/participations\/123\/abstaining/.test(url) === true && method === 'GET') {
        return {
            response: ref(Profiles),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/costs\/profile\/[a-zA-Z0-9]+/) {
        return {
            response: ref(HashedProfile.profile),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test profilesStore', () => {
    it('should not contain data before fetching', () => {
        const { ProfilesState } = useProfiles(WEEK_ID);

        expect(ProfilesState.profiles).toEqual([]);
        expect(ProfilesState.isLoading).toBeFalsy();
        expect(ProfilesState.error).toEqual('');
    });

    it('should contain data after fetching', async () => {
        const { ProfilesState, fetchAbsentingProfiles } = useProfiles(WEEK_ID);

        await fetchAbsentingProfiles();
        expect(ProfilesState.profiles).toEqual(Profiles);
        expect(ProfilesState.isLoading).toBeFalsy();
        expect(ProfilesState.error).toEqual('');
    });

    it('should return the profile for a given hash and have no errors', async () => {
        const { ProfilesState, fetchProfileWithHash } = useProfiles(WEEK_ID);

        const profile = await fetchProfileWithHash(HashedProfile.hash);
        expect(profile).toEqual(HashedProfile.profile);
        expect(ProfilesState.error).toEqual('');
    });
});
