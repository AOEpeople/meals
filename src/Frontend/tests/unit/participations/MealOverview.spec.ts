import useApi from '@/api/api';
import { describe, jest, it } from '@jest/globals';
import { ref } from 'vue';
import nextThreeDays from '../fixtures/nextThreeDays.json';
import participations from '../fixtures/participations.json';
import { flushPromises, mount } from '@vue/test-utils';
import MealOverView from '@/components/participations/MealOverview.vue';
import { getShowParticipations } from '@/api/getShowParticipations';
import MealsSummary from '@/components/participations/MealsSummary.vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (url: string) => {
    switch (url) {
        case 'api/meals/nextThreeDays':
            return {
                response: ref(nextThreeDays.dataOne),
                request: asyncFunc,
                error: ref(false)
            };
        case '/api/print/participations':
            return {
                response: ref(participations),
                request: asyncFunc,
                error: ref(false)
            };
        default:
            return {};
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(url));

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
