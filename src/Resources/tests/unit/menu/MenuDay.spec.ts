import MenuDay from '@/components/menu/MenuDay.vue';
import { mount } from '@vue/test-utils';
import { DayDTO } from '@/interfaces/DayDTO';
import { Ref, ref } from 'vue';
import useApi from '@/api/api';
import Weeks from '../fixtures/getWeeks.json';
import DishesCount from '../fixtures/dishesCount.json';
import Dishes from '../fixtures/getDishes.json';
import Categories from '../fixtures/getCategories.json';
import { useDishes } from '@/stores/dishesStore';
import { useCategories } from '@/stores/categoriesStore';
import { useWeeks } from '@/stores/weeksStore';

const { fetchCategories } = useCategories();
const { fetchDishes } = useDishes();
const { fetchWeeks } = useWeeks();

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (url.includes('api/weeks') && method === 'GET') {
        return {
            response: ref(Weeks),
            request: asyncFunc,
            error: false
        };
    } else if (url.includes('api/meals/count') && method === 'GET') {
        return {
            response: ref(DishesCount),
            request: asyncFunc,
            error: false
        };
    } else if (url.includes('api/weeks/') && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/menu/') && method === 'PUT') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/dishes') && method === 'GET') {
        return {
            response: ref(Dishes),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (url.includes('api/categories') && method === 'GET') {
        return {
            response: ref(Categories),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

const testDay: Ref<DayDTO> = ref({
    meals: {
        13: [{ dishSlug: Dishes[0].slug, mealId: 807, participationLimit: 0 }],
        14: [{ dishSlug: Dishes[1].slug, mealId: 808, participationLimit: 0 }]
    },
    enabled: true,
    id: 286,
    event: null,
    date: { date: '2023-07-10 12:00:00.000000', timezone_type: 3, timezone: 'Europe/Berlin' },
    lockDate: { date: '2023-07-9 12:00:00.000000', timezone_type: 3, timezone: 'Europe/Berlin' }
});

describe('Test MenuDay', () => {
    beforeEach(async () => {
        await fetchCategories();
        await fetchDishes();
        await fetchWeeks();
    });

    it('should render MenuDay', () => {
        const wrapper = mount(MenuDay, {
            props: {
                modelValue: testDay.value
            }
        });
        expect(wrapper.text()).toMatch(/Mon/);
        expect(wrapper.text()).toMatch(/menu.enableDay/);
    });
});
