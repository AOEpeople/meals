import useApi from '@/api/api';
import type { IProfile } from '@/stores/profilesStore';
import type { Dictionary } from '@/types/types';
import { onMounted, readonly, ref, getCurrentInstance } from 'vue';

/**
 * if date is passed participants list is specific to that date, if not it returns the list of today's participants
 * @param date
 * @returns list of participants
 */
export function useParticipationsListData(date: string) {
    const listDataState = ref<IProfile[]>([]);
    const loaded = ref(false);
    const useParticipationsError = ref(false);

    async function getListData() {
        if (date === undefined) {
            return;
        }

        const {
            error,
            response: listData,
            request
        } = useApi<Dictionary<IProfile>>('GET', `/api/participations/day/${date}`);
        useParticipationsError.value = error.value;

        if (loaded.value === false) {
            await request();
            loaded.value = true;

            listDataState.value = Object.values(listData.value ?? {});
        }
    }

    // Check if we're in a component context
    const instance = getCurrentInstance();
    if (instance) {
        onMounted(() => {
            getListData();
        });
    } else {
        // Fallback for tests or non-component usage
        getListData();
    }

    return {
        useParticipationsError,
        listData: readonly(listDataState),
        getListData
    };
}
