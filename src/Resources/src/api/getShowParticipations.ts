import { Dictionary } from "types/types"
import { reactive, readonly } from "vue"
import { DateTime } from "./getDashboardData"

export interface IParticipationsState {
    data: Dictionary<Dictionary<boolean>>,
    meals: Dictionary<IMealData>,
    day: DateTime
}

export interface IMealData {
    title: {
        en: string,
        de: string
    },
    parent?: number,
    participations?: number
}

interface ILoadedState {
    loaded: boolean,
    error: string
}

export interface IMealWithVariations {
    title: {
        en: string,
        de: string
    },
    variations: IMealData[],
    participations: number
}

const participationsState = reactive<IParticipationsState>({
    data: {},
    meals: {},
    day: {
        date: "",
        timezone_type: 0,
        timezone: ""
    }
});

const loadedState = reactive<ILoadedState>({
    loaded: false,
    error: ""
})

export function getShowParticipations() {

    function getMealsWithVariations() {
        const meals: IMealWithVariations[] = [];
        if(loadedState.loaded) {
            for(const [key, value] of Object.entries(participationsState.meals)) {
                if(!value.parent) {
                    meals.push(createMealWithVariations(key));
                } else if(value.parent === null && value.participations) {
                    const mealWithoutParent: IMealWithVariations = {
                        title: {
                            en: value.title.en,
                            de: value.title.de
                        },
                        variations: [],
                        participations: value.participations
                    }
                    meals.push(mealWithoutParent);
                }
            }
        }
        return meals;
    }
    
    function createMealWithVariations(mealKey: string): IMealWithVariations {
        const mealWithVariations: IMealWithVariations = {
            title: {
                en: participationsState.meals[mealKey].title.en,
                de: participationsState.meals[mealKey].title.de
            },
            variations: getVariationsOfMeal(mealKey),
            participations: 0
        }
        return mealWithVariations;
    }

    function getVariationsOfMeal(parentKey: string) {
        const variations: IMealData[] = [];
        for(const value of Object.values(participationsState.meals)) {
            if(value.parent && value.parent === Number.parseInt(parentKey)) {
                variations.push(value);
            }
        }
        return variations;
    }

    async function loadShowParticipations() {
        if(loadedState.loaded) {
            return true;
        }
        try {
            const controller = new AbortController();
            const URL = `${window.location.origin}/api/print/participations`;

            const timeoutId = setTimeout(() => controller.abort(), 1000);

            const response = await fetch(URL, {
                method: 'GET',
                headers: {
                    "Content-Type": "application/json",
                },
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if(!response.ok) {
                throw new Error("Getting the list of participants failed!");
            }

            const jsonObject: IParticipationsState = await response.json();
            participationsState.data = jsonObject.data;
            participationsState.day = jsonObject.day;
            participationsState.meals = jsonObject.meals;
            loadedState.loaded = true;

        } catch(error) {
            if(error instanceof Error) {
                loadedState.error = error.message;
            } else {
                loadedState.error = 'Unknown error occured';
            }
            loadedState.loaded = false;
        }
    }

    return {
        participationsState: readonly(participationsState),
        loadedState: readonly(loadedState),
        loadShowParticipations,
        getMealsWithVariations
    }
}