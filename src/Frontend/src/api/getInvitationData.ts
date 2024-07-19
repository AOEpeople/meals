import useApi from '@/api/api';
import { ref } from 'vue';
import { Day } from '@/api/getDashboardData';

export type GuestDay = Day;

export async function useInvitationData(hash: string) {
    const { response: invitation, request, error } = useApi<GuestDay>('GET', '/api/guest-invitation-' + hash);

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { invitation, error };
}
