import { nextTick, ref } from 'vue';
import Participations from '../fixtures/menuParticipations.json';
import Weeks from '../fixtures/getWeeks.json';
import Dishes from '../fixtures/getDishes.json';
import MenuTable from '@/components/menuParticipants/MenuTable.vue';
import { flushPromises, shallowMount } from "@vue/test-utils";
import useApi from '@/api/api';

const asyncFunc: () => Promise<void> = async () => {
    new Promise(resolve => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/participations\/\d+$/.test(url) === true && method === 'GET') {
        return {
            response: ref(Participations),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('api/weeks') && method === 'GET') {
        return {
            response: ref(Weeks),
            request: asyncFunc,
            error: ref(false)
        }
    } else if (url.includes('api/dishes') && method === 'GET') {
        return {
            response: ref(Dishes),
            request: asyncFunc,
            error: ref(false)
        }
    }
}

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test MenuTable', () => {
    it('should render the table without errors', async () => {
        const wrapper = shallowMount(MenuTable, {
            props: {
                weekId: 1
            }
        });

        await flushPromises();
        setTimeout(async () => {
            await nextTick();
            expect(wrapper.vm.loaded).toBe(true);
            expect(useApi).toHaveBeenCalledTimes(3);
            expect(wrapper.find('table').exists()).toBe(true);
        }, 1000);
    });
});
