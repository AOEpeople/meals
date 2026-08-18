import useApi from '@/api/api';
import { ref } from 'vue';

export type UserData = {
    id: number | null;
    roles: Array<string>;
    fullName: string | null;
    balance: number;
};

export async function useUserData() {
    const { response: userData, request, error } = useApi<UserData>('GET', 'api/user');

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { userData, error };
}
