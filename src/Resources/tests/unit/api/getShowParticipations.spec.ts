import { ref } from 'vue';
import participations from '../fixtures/participations.json';
import { getShowParticipations } from '@/api/getShowParticipations';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(participations),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getShowParticipations', () => {
    it('should fetch the participations and put them in the participationsState', async () => {
        const { participationsState, loadShowParticipations } = getShowParticipations();

        // inititial state should be empty
        expect(participationsState.data).toEqual({});
        expect(participationsState.meals).toEqual({});
        expect(participationsState.day.date).toBe('');
        expect(participationsState.day.timezone_type).toBe(0);
        expect(participationsState.day.timezone).toBe('');

        await loadShowParticipations();

        // participationsState should be filled after fetching
        expect(participations.data).toEqual(participationsState.data);
        expect(participations.meals).toEqual(participationsState.meals);
        expect(participations.day).toEqual(participationsState.day);
    });

    it('should return a list of bookable meals', async () => {
        const { loadShowParticipations, getListOfBookableMeals } = getShowParticipations();
        const TESTCASES = [
            'Century Eggs, paired with a compote of seasonal berries and rye bread #v1',
            'Century Eggs, paired with a compote of seasonal berries and rye bread #v2',
            'Tasty Worms',
            'Combined Dish'
        ];

        await loadShowParticipations();
        const listOfMeals = getListOfBookableMeals();

        expect(listOfMeals).toHaveLength(TESTCASES.length);
        for (const meal of listOfMeals) {
            expect(TESTCASES).toContain(meal.title.en);
        }
    });

    it('should return a list of all meals', async () => {
        const { loadShowParticipations, getMealsWithVariations } = getShowParticipations();
        const TESTCASES = [
            'Century Eggs, paired with a compote of seasonal berries and rye bread',
            'Tasty Worms',
            'Combined Dish'
        ];
        const TEST_VARIATIONS = [
            'Century Eggs, paired with a compote of seasonal berries and rye bread #v1',
            'Century Eggs, paired with a compote of seasonal berries and rye bread #v2'
        ];

        await loadShowParticipations();
        const listOfMeals = getMealsWithVariations();

        expect(listOfMeals).toHaveLength(TESTCASES.length);
        for (const meal of listOfMeals) {
            expect(TESTCASES).toContain(meal.title.en);
            if (meal.title.en === 'Century Eggs, paired with a compote of seasonal berries and rye bread') {
                expect(meal.variations).toHaveLength(2);
                for (const variation of meal.variations) {
                    expect(TEST_VARIATIONS).toContain(variation.title.en);
                }
            }
        }
    });

    it('should return the current day from the participationsState', async () => {
        const { getCurrentDay, loadShowParticipations } = getShowParticipations();
        const TEST_DATE = new Date('2023-04-28 12:00:00.000000');

        await loadShowParticipations();
        const currentDate = getCurrentDay();

        expect(TEST_DATE).toEqual(currentDate);
    });
});
