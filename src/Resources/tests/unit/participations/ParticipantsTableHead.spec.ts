import ParticipantsTableHead from '@/components/participations/ParticipantsTableHead.vue';
import participations from '../fixtures/participations.json';
import { ref } from 'vue';
import { flushPromises, mount, shallowMount } from '@vue/test-utils';
import { getShowParticipations } from '@/api/getShowParticipations';
import MealHead from '@/components/participations/MealHead.vue';
import { describe, vi, it, expect } from 'vitest';

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

describe('Test ParticipantsTableHead', () => {

    it('should render a MealHead for each Meal', async () => {
        const { loadShowParticipations } = getShowParticipations();
        const wrapper = shallowMount(ParticipantsTableHead);

        await loadShowParticipations();
        await flushPromises();

        expect(wrapper.findAllComponents(MealHead)).toHaveLength(3);
    });

    it('should render four th-elements', async () => {
        const { loadShowParticipations } = getShowParticipations();
        const wrapper = shallowMount(ParticipantsTableHead);

        await loadShowParticipations();
        await flushPromises();

        expect(wrapper.findAll('[data-test="meal-head-th"]')).toHaveLength(4);
    });

    it('should contain the correct meal titles', async () => {
        const testmealTitles = [participations.meals[6].title.en, participations.meals[14].title.en, 'Combi'];

        const { loadShowParticipations } = getShowParticipations();
        const wrapper = mount(ParticipantsTableHead);

        await loadShowParticipations();
        await flushPromises();

        const foundMealTitles = wrapper.findAll('.meal-header-test');

        expect(foundMealTitles).toHaveLength(3);
        for (const foundMealTitle of foundMealTitles) {
            expect(testmealTitles.includes(foundMealTitle.text())).toBe(true);
        }
    });
});
