import useSessionStorage from '@/services/useSessionStorage';

export default async function checkActiveSession(stringToStore: string | null = null) {
    try {
        const response = await fetch(window.location.href);
        if (response.status !== 200) {
            saveAndReload(stringToStore);
        }
    } catch (error) {
        saveAndReload(stringToStore);
    }
}

function saveAndReload(stringToStore: string | null = null) {
    if (stringToStore !== null) {
        useSessionStorage().saveData(stringToStore);
    }
    window.location.reload();
}
