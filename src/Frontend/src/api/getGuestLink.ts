import useApi from '@/api/api';
import { ref } from 'vue';

export type Link = {
    url: string;
};

export async function useGuestLink(dayId: string) {
    const { response: link, request, error } = useApi<Link>('GET', '/menu/' + dayId + '/new-guest-invitation');

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { link, error };
}
