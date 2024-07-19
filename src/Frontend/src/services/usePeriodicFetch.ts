import { Ref, ref, watch } from 'vue';

/**
 * Periodically performs a passed in callback if periodicFetchActive is set to true
 * @param timeoutPeriod time between the callbacks
 * @param fetchFunction callback to perform
 * @returns ref-object periodicFetchActive
 */
export function usePeriodicFetch(timeoutPeriod: number, fetchFunction: () => Promise<void>) {
    const periodicFetchActive = ref(false);

    const fetchID: Ref<number | null> = ref(null);

    /**
     * Watcher that activates the periodicFetch-function when periodicFetchActive is set to true
     * and was false before that
     */
    watch(periodicFetchActive, (newPeriodicFetchActive, oldPeriodicFetchActive) => {
        if (newPeriodicFetchActive === true && newPeriodicFetchActive !== oldPeriodicFetchActive) {
            periodicFetch();
        } else if (newPeriodicFetchActive === false && fetchID.value !== null) {
            window.clearTimeout(fetchID.value);
        }
    });

    /**
     * Uses the passed in callback periodically, ends when periodicFetchActive is set to false
     */
    async function periodicFetch() {
        if (periodicFetchActive.value === true) {
            fetchID.value = window.setTimeout(async () => {
                await fetchFunction();
                periodicFetch();
            }, timeoutPeriod);
        }
    }

    return {
        periodicFetchActive
    };
}
