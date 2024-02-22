import { ref } from 'vue';

const lockedIds = ref<Set<string>>(new Set());

/**
 * Utility service to provide a state for managing ids,
 * that can be used to implement a locking mechanism.
 */
export function useLockRequests() {
    function addLock(id: string) {
        lockedIds.value.add(id);
        console.log(`Added Lock: ${id}`);
    }

    function removeLock(id: string) {
        setTimeout(() => lockedIds.value.delete(id), 250);
        // lockedIds.value.delete(id);
        console.log(`removed Lock: ${id}`);
    }

    function isLocked(id: string) {
        console.log(`Check Lock: ${id}`);
        return lockedIds.value.has(id);
    }

    return {
        addLock,
        removeLock,
        isLocked
    };
}
