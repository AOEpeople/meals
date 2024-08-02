import { ref } from 'vue';
import Participations from '../fixtures/menuParticipations.json';
import Weeks from '../fixtures/menuWeeks.json';
import Dishes from '../fixtures/menuDishes.json';
import { mount } from '@vue/test-utils';
import { useParticipations } from '@/stores/participationsStore';
import { useWeeks } from '@/stores/weeksStore';
import MenuTableDataRows from '@/components/menuParticipants/MenuTableDataRows.vue';
import { vi, describe, it, expect } from 'vitest';

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

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test MenuTableDataRows', () => {
    const { fetchParticipations } = useParticipations(115);
    const { fetchWeeks } = useWeeks();

    it('should render the table row without errors', async () => {
        await fetchParticipations();
        await fetchWeeks();

        const wrapper = mount(MenuTableDataRows, {
            props: {
                weekId: 115,
                participant: 'Meals, Alice'
            }
        });

        // rows only render after 300ms
        await new Promise((resolve) => setTimeout(resolve, 500));

        expect(wrapper.text()).toContain('Meals, Alice');
        expect(wrapper.findAll('td').length).toBe(16);
    });
});
