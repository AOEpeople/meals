import { ref } from 'vue';
import Participations from '../fixtures/menuParticipations.json';
import Weeks from '../fixtures/menuWeeks.json';
import Dishes from '../fixtures/menuDishes.json';
import { mount } from '@vue/test-utils';
import useApi from '@/api/api';
import { useParticipations } from '@/stores/participationsStore';
import { useWeeks } from '@/stores/weeksStore';
import { useDishes } from '@/stores/dishesStore';
import MenuTableBody from '@/components/menuParticipants/MenuTableBody.vue';

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
    } else if (url.includes('api/dishes') && method === 'GET') {
        return {
            response: ref(Dishes),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test MenuTableBody', () => {
    const { fetchParticipations, getParticipants } = useParticipations(115);
    const { fetchWeeks } = useWeeks();
    const { fetchDishes } = useDishes();

    it('should render the table row without errors', async () => {
        await fetchParticipations();
        await fetchWeeks();
        await fetchDishes();

        const wrapper = mount(MenuTableBody, {
            props: {
                weekId: 115
            }
        });

        // rows only render after 300ms
        await new Promise((resolve) => setTimeout(resolve, 500));

        expect(wrapper.text()).toContain('menu.total');
        for (const participant of getParticipants()) {
            expect(wrapper.text()).toContain(participant);
        }
    });
});
