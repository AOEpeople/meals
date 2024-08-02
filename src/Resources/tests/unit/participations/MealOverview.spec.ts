import { ref } from 'vue';
import nextThreeDays from '../fixtures/nextThreeDays.json';
import participations from '../fixtures/participations.json';
import { flushPromises, mount } from '@vue/test-utils';
import MealOverView from '@/components/participations/MealOverview.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import MealsSummary from '@/components/participations/MealsSummary.vue';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(nextThreeDays.dataOne),
    request: asyncFunc,
    error: ref(false)
};


vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

vi.mock('./getPrintParticipations', () => ({
    default: vi.fn(() => new Promise((resolve) => resolve({
            response: ref(participations),
            error: ref(false)
        })
    ))
}));

describe('Test MealOverView', () => {
    const { loadShowParticipations } = getShowParticipations();

    it('should render three MealSummaries', async () => {
        await loadShowParticipations();

        const wrapper = mount(MealOverView);
        const testNames = ['Wednesday', 'Thursday', 'Friday'];

        await flushPromises();

        expect(wrapper.findAllComponents(MealsSummary)).toHaveLength(3);

        const listOfHeaders = wrapper.findAll('th');
        expect(listOfHeaders).toHaveLength(3);

        for (const th of listOfHeaders) {
            expect(testNames.includes(th.text())).toBe(true);
        }
    });
});
