export default function useSessionStorage() {
    const STORAGE_KEY = 'storedMealsString';

    function saveData(dataToSave: string) {
        sessionStorage.setItem(STORAGE_KEY, dataToSave);
    }

    function getData(): string | null {
        return sessionStorage.getItem(STORAGE_KEY);
    }

    function clearData() {
        sessionStorage.removeItem(STORAGE_KEY);
    }

    function getAndClearData() {
        const data = getData();
        clearData();
        return data;
    }

    return {
        saveData,
        getAndClearData
    };
}
