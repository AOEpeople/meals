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

    function setFilter(filterStr: string) {
        DishesState.filter = filterStr;
    }

    const filteredDishes = computed(() => {
        const { getCategoryIdsByTitle } = useCategories();
        return DishesState.dishes.filter(dish => dishContainsString(dish, DishesState.filter) || getCategoryIdsByTitle(DishesState.filter).includes(dish.categoryId));
    });

    function dishContainsString(dish: Dish ,searchStr: string) {
        return (
            dish.titleDe.toLowerCase().includes(searchStr.toLowerCase())
            || dish.titleEn.toLowerCase().includes(searchStr.toLowerCase())
            || dish.variations.map(variation => dishContainsString(variation, searchStr)).includes(true)
        );
    }

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

    async function createDish(dish: CreateDishDTO) {
        const { error, response } = await postCreateDish(dish);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on creating dish';
            return;
        }

        await fetchDishes();
    }

    async function deleteDishWithSlug(slug: string) {
        const { error, response } = await deleteDish(slug);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on creating dish';
            return;
        }

        await fetchDishes();
    }

    async function createDishVariation(dishVariation: CreateDishVariationDTO, parentSlug: string) {
        const { error, response } = await postCreateDishVariation(dishVariation, parentSlug);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on creating dish variation';
            return;
        }

        await fetchDishes();
    }

    async function deleteDishVariationWithSlug(slug: string) {
        const { error, response } = await deleteDishVariation(slug);

        if (error.value || response.value?.status !== 'success') {
            DishesState.error = 'Error on deleting dish variation';
            return;
        }

        await fetchDishes();
    }

    async function updateDishVariation(slug: string, variation: CreateDishVariationDTO) {
        const { error, response } = await putDishVariationUpdate(slug, variation);

        if (!error.value && response.value && response.value.parentId) {
            updateDishVariationInState(response.value.parentId, response.value);
        } else {
            DishesState.error = 'Error on updating dishVariation';
        }
    }

    async function updateDish(id: number, dish: CreateDishDTO) {
        const { error, response } = await putDishUpdate(getDishById(id).slug, dish);

        if (!error.value && response.value) {
            updateDishesState(response.value);
        } else {
            DishesState.error = 'Error on updating a dish';
        }
    }

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

    function updateDishVariationInState(parentId: number, variation: Dish) {
        const varationToUpdate: Dish = getDishVariationByParentIdAndId(parentId, variation.id);

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
        setFilter
    };
}