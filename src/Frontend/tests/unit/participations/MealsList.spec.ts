import participations from '../fixtures/participations.json';
import MealsList from '@/components/participations/MealsList.vue';
import Meal from '@/components/participations/Meal.vue';
import { describe } from '@jest/globals';
import { ref } from 'vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { flushPromises, mount } from '@vue/test-utils';
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(participations),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const mockSetMealsListHeight = jest.fn((height: number, elementId: string) => void 0);
jest.mock('@/services/useComponentHeights', () => ({
    useComponentHeights: () => ({
        setMealListHight: mockSetMealsListHeight,
        windowWidth: ref(1080)
    })
}));

describe('Test MealsList', () => {
    const { loadShowParticipations } = getShowParticipations();
    beforeEach(async () => {
        await loadShowParticipations();
    });

    it('should render a Meal-Component for each Meal', async () => {
        const wrapper = mount(MealsList);

        await flushPromises();

        expect(wrapper.findAllComponents(Meal)).toHaveLength(3);
    });

    it('should call setMealListHight', async () => {
        mount(MealsList);

        await flushPromises();

        expect(mockSetMealsListHeight).toHaveBeenCalled();
    });
});
