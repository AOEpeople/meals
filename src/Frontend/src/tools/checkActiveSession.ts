import useSessionStorage from '@/services/useSessionStorage';

export default async function checkActiveSession(stringToStore: string | null = null) {
    try {
        const isActive = await isSessionActive();
        if (isActive === false) {
            saveAndReload(stringToStore);
        }
    } catch (error) {
        saveAndReload(stringToStore);
    }
}

export function saveAndReload(stringToStore: string | null = null) {
    if (stringToStore !== null) {
        useSessionStorage().saveData(stringToStore);
    }
    window.location.reload();
}

export async function isSessionActive() {
    const response = await fetch(window.location.href);
    return response.status === 200;
}
