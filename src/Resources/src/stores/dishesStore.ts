import getDishes from "@/api/getDishes";
import { reactive, readonly } from "vue";
import postCreateDish, { CreateDishDTO } from "@/api/postCreateDish";
import deleteDish from "@/api/deleteDish";
import putDishUpdate from "@/api/putDishUpdate";

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
    isLoading: boolean,
    error: string
}

const TIMEOUT_PERIOD = 10000;

const DishesState = reactive<DishesState>({
    dishes: [],
    isLoading: false,
    error: ''
});

export function useDishes() {

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

    function getDishById(id: number) {
        return DishesState.dishes.find(dish => dish.id === id);
    }

    return {
        DishesState: readonly(DishesState),
        fetchDishes,
        createDish,
        deleteDishWithSlug,
        updateDish
    };
}