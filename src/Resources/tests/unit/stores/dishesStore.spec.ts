import { useDishes, Dish } from "@/stores/dishesStore";
import { useCategories } from "@/stores/categoriesStore";
import useApi from "@/api/api";
import { ref } from "vue";
import { beforeAll, beforeEach, describe, it } from "@jest/globals";
import success from "../fixtures/Success.json";
import Dishes from "../fixtures/getDishes.json";
import Categories from "../fixtures/getCategories.json";

const dish1: Dish = {
    id: 0,
    slug: "",
    titleDe: "",
    titleEn: "",
    categoryId: 0,
    oneServingSize: false,
    parentId: 0,
    variations: []
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
    const { resetState, DishesState, fetchDishes } = useDishes();
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
});