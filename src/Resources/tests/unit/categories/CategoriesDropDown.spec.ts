import CategoriesDropDown from '@/components/categories/CategoriesDropDown.vue';
import { ref } from 'vue';
import Categories from '../fixtures/getCategories.json';
import { useCategories } from '@/stores/categoriesStore';
import { mount } from '@vue/test-utils';
import { Listbox, ListboxButton, ListboxOption } from '@headlessui/vue';
import { vi, describe, beforeAll, it, expect } from 'vitest';

const { fetchCategories } = useCategories();

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Categories),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test CategoriesDropDown', () => {
    beforeAll(async () => {
        await fetchCategories();
    });

    it('should contain all categories from the state', async () => {
        const wrapper = mount(CategoriesDropDown);

        await wrapper.findComponent(ListboxButton).trigger('click');

        const options = wrapper.findAllComponents(ListboxOption);
        expect(options).toHaveLength(Categories.length);

        options.forEach((option, index) => {
            expect(option.text()).toMatch(Categories[index].titleEn);
        });
    });

    it('should contain the correct category after selecting it', async () => {
        const wrapper = mount(CategoriesDropDown);

        await wrapper.findComponent(ListboxButton).trigger('click');
        const options = wrapper.findAllComponents(ListboxOption);

        for (const [index, option] of options.entries()) {
            await option.trigger('click');

            expect(wrapper.findComponent(Listbox).props().modelValue).toEqual(Categories[index]);
        }
    });
});
