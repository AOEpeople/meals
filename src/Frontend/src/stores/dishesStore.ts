import deleteDish from '@/api/deleteDish';
import deleteDishVariation from '@/api/deleteDishVariation';
import getDishes from '@/api/getDishes';
import { type Ref, computed, reactive, readonly, ref, watch } from 'vue';
import postCreateDish, { type CreateDishDTO } from '@/api/postCreateDish';
import postCreateDishVariation, { type CreateDishVariationDTO } from '@/api/postCreateDishVariation';
import putDishUpdate from '@/api/putDishUpdate';
import putDishVariationUpdate from '@/api/putDishVariationUpdate';
import { isMessage } from '@/interfaces/IMessage';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import { refThrottled } from '@vueuse/core';
import getDishesForCombi from '@/api/getDishesForCombi';
import { useCategories } from './categoriesStore';
import { isResponseArrayOkay, isResponseObjectOkay } from '@/api/isResponseOkay';

export interface Dish {
    id: number;
    slug: string;
    titleDe: string;
    titleEn: string;
    descriptionDe?: string;
    descriptionEn?: string;
    categoryId: number;
    oneServingSize: boolean;
    parentId: number;
    variations: Dish[];
}

interface DishesState {
    dishes: Dish[];
    isLoading: boolean;
    error: string;
}

function isDish(dish: Dish): dish is Dish {
    return (
        dish !== null &&
        dish !== undefined &&
        typeof (dish as Dish).id === 'number' &&
        typeof (dish as Dish).slug === 'string' &&
        typeof (dish as Dish).titleDe === 'string' &&
        typeof (dish as Dish).titleEn === 'string' &&
        ((dish as Dish).categoryId === null || typeof (dish as Dish).categoryId === 'number') &&
        typeof (dish as Dish).oneServingSize === 'boolean' &&
        Array.isArray((dish as Dish).variations) &&
        Object.keys(dish).length >= 8 &&
        Object.keys(dish).length <= 10
    );
}

const TIMEOUT_PERIOD = 10000;

const DishesState = reactive<DishesState>({
    dishes: [],
    isLoading: false,
    error: ''
});

const filterState = ref('');
const dishFilter = refThrottled(filterState, 1000);

const { sendFlashMessage } = useFlashMessage();

watch(
    () => DishesState.error,
    () => {
        if (DishesState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: DishesState.error
            });
        }
    }
);

export function useDishes() {
    /**
     * Updates the string to filter for in the DishesState
     */
    function setFilter(filterStr: string) {
        // DishesState.filter = filterStr;
        filterState.value = filterStr;
    }

    /**
     * Returns a list of dishes that contain the filter string in their title, in the title of their variations or in the title of their category
     */
    const filteredDishes = computed(() => {
        const { getCategoryIdsByTitle } = useCategories();
        return DishesState.dishes.filter(
            (dish) =>
                (dishContainsString(dish, dishFilter.value) ||
                    getCategoryIdsByTitle(dishFilter.value).includes(dish.categoryId)) &&
                dish.slug !== 'combined-dish'
        );
    });

    /**
     * Determines wether a dish contains the search string in its title or in the title of one of its variations
     */
    function dishContainsString(dish: Dish, searchStr: string) {
        return (
            dish.titleDe.toLowerCase().includes(searchStr.toLowerCase()) ||
            dish.titleEn.toLowerCase().includes(searchStr.toLowerCase()) ||
            dish.variations.map((variation) => dishContainsString(variation, searchStr)).includes(true)
        );
    }

    /**
     * Fetches a list of dishes from the API and updates the DishesState
     */
    async function fetchDishes() {
        DishesState.isLoading = true;

        const { dishes, error } = await getDishes();
        if (isResponseArrayOkay<Dish>(error, dishes, isDish) === true) {
            DishesState.dishes = dishes.value;
            DishesState.error = '';
        } else {
            DishesState.error = 'Error on fetching dishes';
            setTimeout(fetchDishes, TIMEOUT_PERIOD);
        }

        DishesState.isLoading = false;
    }

    /**
     * Calls postCreateDish to create a dish and fetches the dishes again
     * @param dish The dish to create
     */
    async function createDish(dish: CreateDishDTO) {
        const { error, response } = await postCreateDish(dish);

        if (error.value === true || isMessage(response.value) === true) {
            DishesState.error = response.value?.message;
            return;
        }

        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'dishes.created'
        });
        await fetchDishes();
    }

    /**
     * Calls deleteDish to delete a dish and fetches the dishes again
     * @param slug The slug of the dish to delete
     */
    async function deleteDishWithSlug(slug: string) {
        const { error, response } = await deleteDish(slug);

        if (error.value === true || isMessage(response.value) === true) {
            DishesState.error = response.value?.message;
            return;
        }

        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'dishes.deleted'
        });
        await fetchDishes();
    }

    /**
     * Creates a dishVariation and fetches the dishes again
     * @param dishVariation The dishVariation to create
     * @param parentSlug The identifier of the parent dish
     */
    async function createDishVariation(dishVariation: CreateDishVariationDTO, parentSlug: string) {
        const { error, response } = await postCreateDishVariation(dishVariation, parentSlug);

        if (error.value === true || isMessage(response.value) === true) {
            DishesState.error = response.value?.message;
            return;
        }

        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'dishes.created'
        });
        await fetchDishes();
    }

    /**
     * Deletes a dishVariation and fetches the dishes again
     * @param slug The identifier of the dishVariation to delete
     */
    async function deleteDishVariationWithSlug(slug: string) {
        const { error, response } = await deleteDishVariation(slug);

        if (error.value === true || isMessage(response.value) === true) {
            DishesState.error = response.value?.message;
            return;
        }

        sendFlashMessage({
            type: FlashMessageType.INFO,
            message: 'dishes.deleted'
        });
        await fetchDishes();
    }

    /**
     * Updates a dishVariation
     * @param slug  The identifier of the dishVariation to update
     * @param variation DTO containing the new values for the dishVariation
     */
    async function updateDishVariation(slug: string, variation: CreateDishVariationDTO) {
        const { error, response } = await putDishVariationUpdate(slug, variation);

        if (
            isMessage(response.value) === false &&
            isResponseObjectOkay<Dish>(error, response as Ref<Dish>, isDish) === true &&
            typeof (response.value as Dish).parentId === 'number'
        ) {
            updateDishVariationInState((response.value as Dish).parentId, response.value as Dish);
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'dishes.updated'
            });
        } else {
            DishesState.error = isMessage(response.value) ? response.value.message : 'Error on updating dishVariation';
        }
    }

    /**
     * Updates a dish
     * @param id The ID of the dish to update
     * @param dish DTO containing the new values for the dish
     */
    async function updateDish(id: number, dish: CreateDishDTO) {
        const { error, response } = await putDishUpdate(getDishById(id).slug, dish);

        if (
            isMessage(response.value) === false &&
            isResponseObjectOkay<Dish>(error, response as Ref<Dish>, isDish) === true
        ) {
            updateDishesState(response.value as Dish);
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'dishes.updated'
            });
        } else {
            DishesState.error = isMessage(response.value) ? response.value.message : 'Error on updating a dish';
        }
    }

    /**
     * Fetches the dishes a combi meal consists of
     * @param mealId The id of the combi-meal
     */
    async function getCombiDishes(combiMealId: number) {
        const { error, response } = await getDishesForCombi(combiMealId);

        if (error.value === true) {
            DishesState.error = 'Error on getting dishes for combi meal';
            return [];
        } else {
            DishesState.error = '';
            return response.value;
        }
    }

    /**
     * Updates the DishesState with the new values of a dish
     */
    function updateDishesState(dish: Dish) {
        const dishToUpdate: Dish = getDishById(dish.id);

        dishToUpdate.slug = dish.slug;
        dishToUpdate.titleDe = dish.titleDe;
        dishToUpdate.titleEn = dish.titleEn;
        dishToUpdate.oneServingSize = dish.oneServingSize;
        dishToUpdate.descriptionDe = dish.descriptionDe;
        dishToUpdate.descriptionEn = dish.descriptionEn;
        dishToUpdate.categoryId = dish.categoryId;
    }

    /**
     * Updates the DishesState with the new values of a dishVariation
     */
    function updateDishVariationInState(parentId: number, variation: Dish) {
        const varationToUpdate: Dish = getDishVariationByParentIdAndId(parentId, variation.id);

        varationToUpdate.slug = variation.slug;
        varationToUpdate.titleDe = variation.titleDe;
        varationToUpdate.titleEn = variation.titleEn;
    }

    function getDishById(id: number) {
        let dishToReturn: Dish | null = null;

        DishesState.dishes.forEach((dish) => {
            if (dish.id === id) {
                dishToReturn = dish;
            }

            dish.variations.forEach((variation) => {
                if (variation.id === id) {
                    dishToReturn = variation;
                }
            });
        });

        return dishToReturn;
    }

    function getDishVariationByParentIdAndId(parentId: number, variationId: number) {
        const parentDish = getDishById(parentId);
        return parentDish.variations.find((variation) => variation.id === variationId);
    }

    function getDishBySlug(slug: string): Dish | null {
        let dishToReturn: Dish | null = null;

        DishesState.dishes.forEach((dish) => {
            if (dish.slug === slug) {
                dishToReturn = dish;
            }

            dish.variations.forEach((variation) => {
                if (variation.slug === slug) {
                    dishToReturn = variation;
                }
            });
        });

        return dishToReturn;
    }

    /**
     * Finds the dishes by their slugs and returns them in arrays of dishes, depending on if their parent matches.
     * If they have the same parent they are returned in the same array with their parent. If they don't have a parent
     * they are returned in an array containing only that dish.
     * @param slugs The slugs of the dishes to return
     */
    function getDishArrayBySlugs(slugs: string[]) {
        const dishesFromSlugs: Dish[] = slugs.map((slug) => getDishBySlug(slug));

        const dishesWithParent: Dish[] = [];

        let parentDishInArray = false;
        for (const dish of dishesFromSlugs) {
            if (dish === null || dish === undefined) {
                continue;
            }
            // If the dish has a parent and the parent is not already in the array, add the parent and the dish to the array
            if (typeof dish.parentId === 'number' && parentDishInArray === false) {
                const parentDish = getDishById(dish.parentId);
                dishesWithParent.push(parentDish, dish);
                parentDishInArray = true;
            } else if (typeof dish.parentId === 'number') {
                // If the dish has a parent and the parent is already in the array, add the dish to the array of the parent
                dishesWithParent.push(dish);
            } else {
                // If the dish has no parent, add it to the array
                dishesWithParent.push(dish);
            }
        }

        return dishesWithParent;
    }

    /**
     * Resets the DishesState.
     * Only used for testing purposes.
     */
    function resetState() {
        DishesState.dishes = [];
        DishesState.error = '';
        DishesState.isLoading = false;
        filterState.value = '';
    }

    return {
        DishesState: readonly(DishesState),
        filterState: readonly(filterState),
        filteredDishes,
        fetchDishes,
        createDish,
        deleteDishWithSlug,
        updateDish,
        createDishVariation,
        deleteDishVariationWithSlug,
        updateDishVariation,
        setFilter,
        resetState,
        getDishBySlug,
        getDishArrayBySlugs,
        getDishById,
        getCombiDishes
    };
}
