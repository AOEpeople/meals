import { ref } from 'vue';
import Participations from '../fixtures/menuParticipations.json';
import Weeks from '../fixtures/menuWeeks.json';
import MenuTableHead from '@/components/menuParticipants/MenuTableHead.vue';
import { shallowMount } from '@vue/test-utils';
import useApi from '@/api/api';
import { useParticipations } from '@/stores/participationsStore';
import { useWeeks } from '@/stores/weeksStore';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/participations\/\d+$/.test(url) === true && method === 'GET') {
        return {
            response: ref(Participations),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/weeks') && method === 'GET') {
        return {
            response: ref(Weeks),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test MenuTableHead', () => {
    const { fetchParticipations } = useParticipations(115);
    const { fetchWeeks } = useWeeks();

    it('should render the table without errors', async () => {
        await fetchParticipations();
        await fetchWeeks();

        const wrapper = shallowMount(MenuTableHead, {
            props: {
                weekId: 115
            }
        });

        expect(wrapper.text()).toContain('8/21 - 8/25');
        expect(wrapper.text()).toContain('Monday');
        expect(wrapper.text()).toContain('Tuesday');
        expect(wrapper.text()).toContain('Wednesday');
        expect(wrapper.text()).toContain('Thursday');
        expect(wrapper.text()).toContain('Friday');
    });
});
