import { useDishes, Dish } from "@/stores/dishesStore";
import { useCategories } from "@/stores/categoriesStore";
import useApi from "@/api/api";
import { ref } from "vue";
import { beforeAll, beforeEach, describe, expect, it } from "@jest/globals";
import success from "../fixtures/Success.json";
import Dishes from "../fixtures/getDishes.json";
import Categories from "../fixtures/getCategories.json";
import { CreateDishDTO } from "@/api/postCreateDish";

const dish1: Dish = {
    id: 17,
    slug: "limbs123",
    titleDe: "Limbs DE 123",
    titleEn: "Limbs 123",
    categoryId: 6,
    oneServingSize: false,
    parentId: null,
    variations: []
};

const dishUpdate: CreateDishDTO = {
    titleDe: "Limbs DE 123",
    titleEn: "Limbs 123",
    oneServingSize: false
};

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/categories') && method === 'GET') {
        return {
            response: ref(Categories),
            request: asyncFunc,
            error: false
        }
    } else if (url.includes('api/dishes') && method === 'GET') {
        return {
            response: ref(Dishes),
            request: asyncFunc,
            error: false
        }
    } else if (url.includes('api/dishes') && (method === 'POST' || method === 'DELETE')) {
        return {
            response: ref(success),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('api/dishes') && method === 'PUT') {
        return {
            response: ref(dish1),
            request: asyncFunc,
            error: ref(false)
        }
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test dishesStore', () => {
    const { resetState, DishesState, fetchDishes, setFilter, filteredDishes, updateDish, getDishBySlug } = useDishes();
    const { fetchCategories } = useCategories();

    beforeAll(async () => {
        await fetchCategories();
    });

    beforeEach(() => {
        resetState();
    });

    it('should not contain data before fetching', () => {
        expect(DishesState.dishes).toEqual([]);
        expect(DishesState.isLoading).toBeFalsy();
        expect(DishesState.error).toEqual('');
        expect(DishesState.filter).toEqual('');
    });

    it('should contain data after fetching', async () => {
        await fetchDishes();

        expect(DishesState.dishes).toEqual(Dishes);
        expect(DishesState.isLoading).toBeFalsy();
        expect(DishesState.error).toEqual('');
        expect(DishesState.filter).toEqual('');
    });

    it('should contain data after fetching and filtering', async () => {
        await fetchDishes();

        expect(filteredDishes.value).toEqual(DishesState.dishes);

        setFilter('testen');
        expect(filteredDishes.value).toContainEqual(Dishes[0]);
        expect(filteredDishes.value).not.toContainEqual(Dishes[1]);
        expect(DishesState.filter).toEqual('testen');

        setFilter('Fish (so juicy sweat)');
        expect(filteredDishes.value).toContainEqual(Dishes[3]);
        expect(filteredDishes.value).not.toContainEqual(Dishes[0]);

        setFilter('Vegetarisch');
        for(const dish of filteredDishes.value) {
            expect(dish.categoryId).toBe(5);
        }
        expect(filteredDishes.value).not.toContainEqual(Dishes[3]);
        expect(filteredDishes.value).toContainEqual(Dishes[0]);
    });

    it('should update the state after sending a PUT request', async () => {
        await fetchDishes();

        await updateDish(17, dishUpdate);

        expect(DishesState.dishes).toContainEqual(dish1);
    });

    it('should get the correct dishes by their respective slug', async () => {
        await fetchDishes();

        expect(getDishBySlug('testen')).toEqual(Dishes[0]);
        expect(getDishBySlug('testvaren')).toEqual(Dishes[0]);
    });
});