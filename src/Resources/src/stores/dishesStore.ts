import getDishes from "@/api/getDishes";
import { computed, reactive, readonly } from "vue";
import postCreateDish, { CreateDishDTO } from "@/api/postCreateDish";
import deleteDish from "@/api/deleteDish";
import putDishUpdate from "@/api/putDishUpdate";
import postCreateDishVariation, { CreateDishVariationDTO } from "@/api/postCreateDishVariation";
import deleteDishVariation from "@/api/deleteDishVariation";
import putDishVariationUpdate from "@/api/putDishVariationUpdate";
import { useCategories } from "./categoriesStore";

export interface Dish {
    id: number,
    slug: string,
    titleDe: string,
    titleEn: string,
    descriptionDe?: string,
    descriptionEn?: string,
    categoryId: number,
    oneServingSize: boolean,
    parentId: number,
    variations: Dish[]
}

interface DishesState {
    dishes: Dish[],
    filter: string,
    isLoading: boolean,
    error: string
}

const TIMEOUT_PERIOD = 10000;

const DishesState = reactive<DishesState>({
    dishes: [],
    filter: '',
    isLoading: false,
    error: ''
});

export function useDishes() {

    /**
     * Updates the string to filter for in the DishesState
     */
    function setFilter(filterStr: string) {
        DishesState.filter = filterStr;
    }

    /**
     * Returns a list of dishes that contain the filter string in their title, in the title of their variations or in the title of their category
     */
    const filteredDishes = computed(() => {
        const { getCategoryIdsByTitle } = useCategories();
        return DishesState.dishes.filter(dish => dishContainsString(dish, DishesState.filter) || getCategoryIdsByTitle(DishesState.filter).includes(dish.categoryId));
    });

    /**
     * Determines wether a dish contains the search string in its title or in the title of one of its variations
     */
    function dishContainsString(dish: Dish ,searchStr: string) {
        return (
            dish.titleDe.toLowerCase().includes(searchStr.toLowerCase())
            || dish.titleEn.toLowerCase().includes(searchStr.toLowerCase())
            || dish.variations.map(variation => dishContainsString(variation, searchStr)).includes(true)
        );
    }

    /**
     * Fetches a list of dishes from the API and updates the DishesState
     */
    async function fetchDishes() {
        DishesState.isLoading = true;

        const { dishes, error } = await getDishes();
        if (!error.value && dishes.value) {
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

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on creating dish';
            return;
        }

        await fetchDishes();
    }

    /**
     * Calls deleteDish to delete a dish and fetches the dishes again
     * @param slug The slug of the dish to delete
     */
    async function deleteDishWithSlug(slug: string) {
        const { error, response } = await deleteDish(slug);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on creating dish';
            return;
        }

        await fetchDishes();
    }

    /**
     * Creates a dishVariation and fetches the dishes again
     * @param dishVariation The dishVariation to create
     * @param parentSlug The identifier of the parent dish
     */
    async function createDishVariation(dishVariation: CreateDishVariationDTO, parentSlug: string) {
        const { error, response } = await postCreateDishVariation(dishVariation, parentSlug);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on creating dish variation';
            return;
        }

        await fetchDishes();
    }

    /**
     * Deletes a dishVariation and fetches the dishes again
     * @param slug The identifier of the dishVariation to delete
     */
    async function deleteDishVariationWithSlug(slug: string) {
        const { error, response } = await deleteDishVariation(slug);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on deleting dish variation';
            return;
        }

        await fetchDishes();
    }

    /**
     * Updates a dishVariation
     * @param slug  The identifier of the dishVariation to update
     * @param variation DTO containing the new values for the dishVariation
     */
    async function updateDishVariation(slug: string, variation: CreateDishVariationDTO) {
        const { error, response } = await putDishVariationUpdate(slug, variation);

        if (!error.value && response.value && response.value.parentId) {
            updateDishVariationInState(response.value.parentId, response.value);
        } else {
            DishesState.error = 'Error on updating dishVariation';
        }
    }

    /**
     * Updates a dish
     * @param id The ID of the dish to update
     * @param dish DTO containing the new values for the dish
     */
    async function updateDish(id: number, dish: CreateDishDTO) {
        const { error, response } = await putDishUpdate(getDishById(id).slug, dish);

        if (!error.value && response.value) {
            updateDishesState(response.value);
        } else {
            DishesState.error = 'Error on updating a dish';
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
        return DishesState.dishes.find(dish => dish.id === id);
    }

    function getDishVariationByParentIdAndId(parentId: number, variationId: number) {
        const parentDish = getDishById(parentId);
        return parentDish.variations.find(variation => variation.id === variationId);
    }

    function getDishBySlug(slug: string) {
        let dishToReturn: Dish | null = null;

        DishesState.dishes.forEach(dish => {
            if (dish.slug === slug) {
                dishToReturn = dish;
            }

            dish.variations.forEach(variation => {
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
        const dishesFromSlugs: Dish[] = slugs.map(slug => getDishBySlug(slug));

        const dishesWithParent: Dish[] = [];

        let parentDishInArray = false;
        for (const dish of dishesFromSlugs) {
            if (!dish) {
                continue;
            }
            // If the dish has a parent and the parent is not already in the array, add the parent and the dish to the array
            if (dish.parentId && !parentDishInArray) {
                const parentDish = getDishById(dish.parentId);
                dishesWithParent.push(parentDish, dish);
                parentDishInArray = true;
            } else if (dish.parentId) {
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
        DishesState.filter = '';
        DishesState.isLoading = false;
    }

    return {
        DishesState: readonly(DishesState),
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
    };
}