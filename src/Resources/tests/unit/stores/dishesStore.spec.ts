import { useDishes, Dish } from '@/stores/dishesStore';
import { useCategories } from '@/stores/categoriesStore';
import useApi from '@/api/api';
import { ref } from 'vue';
import { beforeAll, beforeEach, describe, expect, it } from '@jest/globals';
import Dishes from '../fixtures/getDishes.json';
import Categories from '../fixtures/getCategories.json';
import { CreateDishDTO } from '@/api/postCreateDish';
import combiDishes from '../fixtures/combiDishes.json';
import { Diet } from '@/enums/Diet';

const dish1: Dish = {
    id: 17,
    slug: 'limbs123',
    titleDe: 'Limbs DE 123',
    titleEn: 'Limbs 123',
    categoryId: 6,
    oneServingSize: false,
    diet: Diet.MEAT,
    parentId: null,
    variations: []
};

const dishUpdate: CreateDishDTO = {
    titleDe: 'Limbs DE 123',
    titleEn: 'Limbs 123',
    oneServingSize: false
};

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/categories') && method === 'GET') {
        return {
            response: ref(Categories),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/dishes') && method === 'GET') {
        return {
            response: ref(Dishes),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/dishes') && (method === 'POST' || method === 'DELETE')) {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/dishes') && method === 'PUT') {
        return {
            response: ref(dish1),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (
        url.includes('api/participations/combi/') &&
        /\S*api\/participations\/combi\/\d*$/.test(url) &&
        method === 'GET'
    ) {
        return {
            response: ref<Dish[]>(combiDishes as Dish[]),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test dishesStore', () => {
    const {
        resetState,
        filterState,
        DishesState,
        fetchDishes,
        setFilter,
        filteredDishes,
        updateDish,
        getDishBySlug,
        getDishArrayBySlugs,
        getCombiDishes
    } = useDishes();
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
        expect(filterState.value).toEqual('');
    });

    it('should contain data after fetching', async () => {
        await fetchDishes();

        expect(DishesState.dishes).toEqual(Dishes);
        expect(DishesState.isLoading).toBeFalsy();
        expect(DishesState.error).toEqual('');
        expect(filterState.value).toEqual('');
    });

    it('should contain data after fetching and filtering', async () => {
        await fetchDishes();

        expect(filteredDishes.value).toEqual(DishesState.dishes);

        setFilter('testen');
        await new Promise((r) => setTimeout(r, 1100));

        expect(filteredDishes.value).toContainEqual(Dishes[0]);
        expect(filteredDishes.value).not.toContainEqual(Dishes[1]);
        expect(filterState.value).toEqual('testen');

        setFilter('Fish (so juicy sweat)');
        await new Promise((r) => setTimeout(r, 1100));
        expect(filteredDishes.value).toContainEqual(Dishes[3]);
        expect(filteredDishes.value).not.toContainEqual(Dishes[0]);

        setFilter('Vegetarisch');
        await new Promise((r) => setTimeout(r, 1100));
        for (const dish of filteredDishes.value) {
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
        expect(getDishBySlug('testvaren')).toEqual(Dishes[0].variations[0]);
    });

    it('should return an array of dishes containing all dishes from the slugs and their parent if they have parents', async () => {
        await fetchDishes();

        const expectedSlugsOne = ['testen', 'testvaren', 'testvaren123'];
        const expectedSlugsTwo = ['testen', 'testvaren'];

        const slugArrOne = ['testvaren', 'testvaren123'];
        const slugArrTwo = ['testvaren'];
        const slugArrThree = ['testen'];

        const dishArrOne = getDishArrayBySlugs(slugArrOne).map((dish) => dish.slug);
        const dishArrTwo = getDishArrayBySlugs(slugArrTwo).map((dish) => dish.slug);
        const dishArrThree = getDishArrayBySlugs(slugArrThree).map((dish) => dish.slug);

        for (const dishSlug of dishArrOne) {
            expect(expectedSlugsOne.includes(dishSlug)).toBeTruthy();
        }

        for (const dishSlug of dishArrTwo) {
            expect(expectedSlugsTwo.includes(dishSlug)).toBeTruthy();
        }

        expect(dishArrThree).toEqual(['testen']);
    });

    it('should return an array of dishes containing the dishes from the fixtures', async () => {
        const dishes = await getCombiDishes(1234);

        expect(dishes).toHaveLength(2);
        expect(dishes).toEqual(combiDishes);
    });
});
