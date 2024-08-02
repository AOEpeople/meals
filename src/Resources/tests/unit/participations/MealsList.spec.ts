import participations from '../fixtures/participations.json';
import MealsList from '@/components/participations/MealsList.vue';
import Meal from '@/components/participations/Meal.vue';
import { ref } from 'vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import { flushPromises, mount } from '@vue/test-utils';
import { vi, describe, beforeEach, it, expect } from 'vitest';

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

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const mockSetMealsListHeight = vi.fn((height: number, elementId: string) => void 0);
vi.mock('@/services/useComponentHeights', () => ({
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
