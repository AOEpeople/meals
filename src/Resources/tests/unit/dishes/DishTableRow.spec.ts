import DishTableRow from '@/components/dishes/DishTableRow.vue';
import { mount } from '@vue/test-utils';
import { describe, it, expect } from '@jest/globals';
import { ref } from 'vue';
import Dishes from '../fixtures/getDishes.json';
import Categories from '../fixtures/getCategories.json';
import useApi from '@/api/api';
import { useCategories } from '@/stores/categoriesStore';
import { Dish } from '@/stores/dishesStore';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Categories),
    request: asyncFunc,
    error: ref(false)
};

jest.mock('@/api/api');
// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi.mockImplementation(() => mockedReturnValue);

const { fetchCategories } = useCategories();

describe('Test DishTableRow', () => {
    beforeAll(async () => {
        await fetchCategories();
    });

    it('should render the dish from the props with no variations', async () => {
        const wrapper = mount(DishTableRow, {
            props: {
                dish: Dishes[4] as Dish,
                indexInList: 4
            }
        });
        // rows only render after 300ms
        await new Promise((resolve) => setTimeout(resolve, 500));
        const tds = wrapper.findAll('td');

        expect(wrapper.findAll('tr').length).toBe(1);
        expect(tds.length).toBe(3);
        tds.forEach((td, index) => {
            if (index === 0) {
                expect(td.text()).toBe(Dishes[4].titleEn);
            } else if (index === 1) {
                expect(td.text()).toBe('Pasta');
            }
        });
    });

    it('should render the dish from the props with variations', async () => {
        const wrapper = mount(DishTableRow, {
            props: {
                dish: Dishes[0] as Dish,
                indexInList: 0
            }
        });
        // rows only render after 300ms
        await new Promise((resolve) => setTimeout(resolve, 500));

        expect(wrapper.findAll('tr').length).toBe(3);
        expect(wrapper.findAll('td').length).toBe(7);
        const titles = [Dishes[0].titleEn, Dishes[0].variations[0].titleEn, Dishes[0].variations[1].titleEn];
        const spans = wrapper.findAll('span');

        spans.forEach((span) => {
            expect(titles).toContainEqual(span.text());
        });
    });

    it('should render the dish variations with topShadow and bottomShadow classes', () => {
        const wrapper = mount(DishTableRow, {
            props: {
                dish: Dishes[0] as Dish,
                indexInList: 0
            }
        });

        const trs = wrapper.findAll('tr');
        expect(trs[1].classes()).toContain('topShadow');
        expect(trs[2].classes()).toContain('bottomShadow');
    });

    it('should render the dish variation with topBottomShadow class', () => {
        const wrapper = mount(DishTableRow, {
            props: {
                dish: Dishes[8] as Dish,
                indexInList: 8
            }
        });

        const tr = wrapper.findAll('tr').at(1);
        expect(tr.classes()).toContain('topBottomShadow');
    });
});
