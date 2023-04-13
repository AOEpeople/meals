import { Dictionary } from "types/types"
import { reactive, readonly } from "vue"
import { DateTime } from "./getDashboardData"

// !! any was used to circumvent a bug caused by exporting as readonly
// TODO: remove any and use IBookedData interface angain
export interface IParticipationsState {
    data: Dictionary<Dictionary<any>>,
    meals: Dictionary<IMealData>,
    day: DateTime
}

export interface IBookedData {
    booked: number[]
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
    variations: IMealWithVariations[],
    participations: number,
    mealId: number
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
     * @returns the current day from the participationState
     */
    function getCurrentDay() {
        return new Date(participationsState.day.date);
    }

    /**
     * @returns A list of strings that represent all the meals of the day
     */
    function getListOfMeals() {
        const mealsSlotOne = Object.keys(participationsState.data)[0];
        const firstParticipant = Object.keys(participationsState.data[mealsSlotOne])[0];
        return Object.keys(participationsState.data[mealsSlotOne][firstParticipant]);
    }

    /**
     * Creates a list of all meals of the day that can be booked.
     * e.g. meals that have variations, can't be booked. Only their variations can.
     * meals that don't have variations are bookable
     * @returns A list of bookable meals
     */
    function getListOfBookableMeals() {
        const listOfBookableMeals: IMealWithVariations[] = [];
        const meals: IMealWithVariations[] = getMealsWithVariations();

        for(const meal of meals) {
            if(meal.variations.length === 0) {
                listOfBookableMeals.push(meal);
            } else {
                meal.variations.forEach((variation) => listOfBookableMeals.push(variation));
            }
        }

        return listOfBookableMeals;
    }

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
                }
            }
        }
        return meals;
    }

    /**
     * Creates an object that conforms to the IMealWithVariations interface, but has no variations
     * @param mealId key to acces the meal in the participationsState.meals
     * @returns A meal without variations
     */
    function createMealWithoutVariations(mealId: string): IMealWithVariations {
        const numberOfParticipants = participationsState.meals[mealId].participations;

        const mealWithVariations: IMealWithVariations = {
            title: {
                en: participationsState.meals[mealId].title.en,
                de: participationsState.meals[mealId].title.de
            },
            variations: [],
            participations: numberOfParticipants  !== undefined ? numberOfParticipants : 0,
            mealId: Number.parseInt(mealId)
        }

        return mealWithVariations;
    }

    /**
     * Bundles a meal with its variations into one object
     * @param mealId key to acces the meal in the participationsState.meals
     * @returns A meal that contains its variations
     */
    function createMealWithVariations(mealId: string): IMealWithVariations {
        const mealWithVariations: IMealWithVariations = {
            title: {
                en: participationsState.meals[mealId].title.en,
                de: participationsState.meals[mealId].title.de
            },
            variations: getVariationsOfMeal(mealId),
            participations: 0,
            mealId: Number.parseInt(mealId)
        }
        return mealWithVariations;
    }

    /**
     * Collects all the variations of a meal
     * @param parentKey key to acces the meal in the participationsState.meals
     * @returns Array of the variations of a meal
     */
    function getVariationsOfMeal(parentKey: string) {
        const variations: IMealWithVariations[] = [];
        for(const [key, value] of Object.entries(participationsState.meals)) {
            if(value.parent && value.parent === Number.parseInt(parentKey)) {
                variations.push(createMealWithoutVariations(key));
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
        getMealsWithVariations,
        getListOfBookableMeals,
        getListOfMeals,
        getCurrentDay
    }
}