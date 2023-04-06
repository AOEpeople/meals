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

/**
 * State containing the meals, and participations per meal for a specific day
 */
const participationsState = reactive<IParticipationsState>({
    data: {},
    meals: {},
    day: {
        date: "",
        timezone_type: 0,
        timezone: ""
    }
});

/**
 * State that contains wether the participationsState was successfully loaded, if not an error is set
 */
const loadedState = reactive<ILoadedState>({
    loaded: false,
    error: ""
})

export function getShowParticipations() {

    /**
     * Bundles all the meals with their variations from the participationsState.meals
     * @returns Array of the parent dishes
     */
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
    
    /**
     * Bundles a meal with its variations into one object
     * @param mealKey key to acces the meal in the participationsState.meals
     * @returns A meal that contains its variations
     */
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

    /**
     * Collects all the variations of a meal
     * @param parentKey key to acces the meal in the participationsState.meals
     * @returns Array of the variations of a meal
     */
    function getVariationsOfMeal(parentKey: string) {
        const variations: IMealData[] = [];
        for(const value of Object.values(participationsState.meals)) {
            if(value.parent && value.parent === Number.parseInt(parentKey)) {
                variations.push(value);
            }
        }
        return variations;
    }

    /**
     * Function performs a GET request to '/api/print/participations' and sets
     * the participationsState if no error occures
     */
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