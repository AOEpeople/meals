import DishesCreationPanel from '@/components/dishes/DishesCreationPanel.vue';
import Dishes from '../fixtures/getDishes.json';
import Categories from '../fixtures/getCategories.json';
import { ref } from 'vue';
import { mount } from '@vue/test-utils';
import CategoriesDropDown from '@/components/categories/CategoriesDropDown.vue';
import useApi from '@/api/api';
import { useCategories } from '@/stores/categoriesStore';
import Switch from '@/components/misc/Switch.vue';
import SubmitButton from '@/components/misc/SubmitButton.vue';

const mockCreateDish = jest.fn();
const mockUpdateDish = jest.fn();
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

jest.mock('@/stores/dishesStore', () => ({
    useDishes: () => ({
        createDish: mockCreateDish,
        updateDish: mockUpdateDish
    })
}));

const { fetchCategories, getCategoryById } = useCategories();

describe('Test DishesCreationPanel', () => {
    beforeAll(async () => {
        await fetchCategories();
    });

    it('should contain a CategoriesDropDown with all categories', async () => {
        const wrapper = mount(DishesCreationPanel, {
            props: {
                titleDe: Dishes[0].titleDe,
                titleEn: Dishes[0].titleEn,
                descriptionDe: Dishes[0].descriptionDe,
                descriptionEn: Dishes[0].descriptionEn,
                categoryId: Dishes[0].categoryId,
                oneSizeServing: Dishes[0].oneServingSize,
                dishId: Dishes[0].id,
                edit: true
            }
        });

        expect(wrapper.findComponent(CategoriesDropDown).exists()).toBe(true);

        await wrapper.findComponent(CategoriesDropDown).trigger('click');
        const categories = Categories.map((category) => category.titleEn);
        const options = wrapper.findAllComponents({ name: 'ListboxOption' });

        options.forEach((option) => {
            expect(categories).toContain(option.text());
        });
    });

    it('should contain inputs with the correct values from the props', async () => {
        const wrapper = mount(DishesCreationPanel, {
            props: {
                titleDe: Dishes[0].titleDe,
                titleEn: Dishes[0].titleEn,
                descriptionDe: Dishes[0].descriptionDe,
                descriptionEn: Dishes[0].descriptionEn,
                categoryId: Dishes[0].categoryId,
                oneSizeServing: Dishes[0].oneServingSize,
                dishId: Dishes[0].id,
                edit: true
            }
        });

        expect((wrapper.find('#dish\\.popover\\.german').element as HTMLInputElement).value).toBe(Dishes[0].titleDe);
        expect((wrapper.find('#dish\\.popover\\.english').element as HTMLInputElement).value).toBe(Dishes[0].titleEn);
        expect((wrapper.find('#dish\\.popover\\.descriptionDe').element as HTMLInputElement).value).toBe(
            Dishes[0].descriptionDe
        );
        expect((wrapper.find('#dish\\.popover\\.descriptionEn').element as HTMLInputElement).value).toBe(
            Dishes[0].descriptionEn
        );
        expect(wrapper.findComponent(CategoriesDropDown).vm.selectedCategory).toBe(
            getCategoryById(Dishes[0].categoryId)
        );
        expect(wrapper.findComponent(Switch).vm.initial).toBe(Dishes[0].oneServingSize);
    });

    it('should not call createDish or updateDish if titles are empty', async () => {
        const wrapper = mount(DishesCreationPanel, {
            props: {
                titleDe: '',
                titleEn: '',
                descriptionDe: Dishes[0].descriptionDe,
                descriptionEn: Dishes[0].descriptionEn,
                categoryId: Dishes[0].categoryId,
                oneSizeServing: Dishes[0].oneServingSize,
                dishId: Dishes[0].id,
                edit: true
            }
        });

        await wrapper.trigger('submit.prevent');

        expect(mockCreateDish).not.toHaveBeenCalled();
        expect(mockUpdateDish).not.toHaveBeenCalled();
    });

    it('should call createDish without edit prop and with an input for the titles', async () => {
        const wrapper = mount(DishesCreationPanel, {
            props: {
                titleDe: Dishes[0].titleDe,
                titleEn: Dishes[0].titleEn,
                descriptionDe: Dishes[0].descriptionDe,
                descriptionEn: Dishes[0].descriptionEn,
                categoryId: Dishes[0].categoryId,
                oneSizeServing: Dishes[0].oneServingSize,
                dishId: Dishes[0].id
            }
        });

        expect(wrapper.findComponent(SubmitButton).exists()).toBe(true);
        await wrapper.trigger('submit.prevent');

        expect(mockCreateDish).toHaveBeenCalled();
    });

    it('should call updateDish with edit prop set to true and with an input for the titles', async () => {
        const wrapper = mount(DishesCreationPanel, {
            props: {
                titleDe: Dishes[0].titleDe,
                titleEn: Dishes[0].titleEn,
                descriptionDe: Dishes[0].descriptionDe,
                descriptionEn: Dishes[0].descriptionEn,
                categoryId: Dishes[0].categoryId,
                oneSizeServing: Dishes[0].oneServingSize,
                dishId: Dishes[0].id,
                edit: true
            }
        });

        await wrapper.trigger('submit.prevent');

        expect(mockUpdateDish).toHaveBeenCalled();
    });
});
