import useApi from "@/api/api";
import { describe, jest, it } from "@jest/globals";
import { computed, ref } from "vue";
import dashboard from "../fixtures/dashboard.json";
import participations from "../fixtures/participations.json";
import { flushPromises, mount } from "@vue/test-utils";
import MealOverView from '@/components/participations/MealOverview.vue';
import { getShowParticipations } from "@/api/getShowParticipations";
import { getDashboardData } from "@/api/getDashboardData";
import MealsSummary from "@/components/participations/MealsSummary.vue";

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (url: string) => {
    switch(url) {
        case "api/dashboard":
            return {
                response: ref(dashboard),
                request: asyncFunc,
                error: ref(false)
            };
        case "/api/print/participations":
            return {
                response: ref(participations),
                request: asyncFunc,
                error: ref(false)
            }
        default:
            return {}
    }
}

jest.mock("vue-i18n", () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(url));

describe('Test MealOverView', () => {

    const { loadShowParticipations } = getShowParticipations();
    const { getDashboard } = getDashboardData();

    it('should render three MealSummaries', async () => {
        await getDashboard();
        await loadShowParticipations();

        const wrapper = mount(MealOverView);
        const testNames = ['Monday', 'Tuesday', 'Wednesday'];

        await flushPromises();

        expect(wrapper.findAllComponents(MealsSummary)).toHaveLength(3);

        const listOfHeaders = wrapper.findAll('th');
        expect(listOfHeaders).toHaveLength(3);

        for(const th of listOfHeaders) {
            expect(testNames.includes(th.text())).toBe(true);
        }
    });
});