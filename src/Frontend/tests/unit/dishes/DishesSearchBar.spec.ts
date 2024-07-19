import DishesSearchBar from '@/components/dishes/DishesSearchBar.vue';
import { describe, it, expect } from '@jest/globals';
import { mount } from '@vue/test-utils';

const mockSetFilter = jest.fn();

jest.mock('@/stores/dishesStore', () => ({
    useDishes: () => ({
        setFilter: mockSetFilter
    })
}));

describe('Test DishesSearchBar', () => {
    it('should render with the correct i18n text', () => {
        const wrapper = mount(DishesSearchBar);

        expect(wrapper.find('input').attributes('placeholder')).toMatch(/dish.search/);
    });

    it('should call setFilter when typing in the input', async () => {
        const wrapper = mount(DishesSearchBar);

        await wrapper.find('input').setValue('test');
        expect(mockSetFilter).toHaveBeenCalledTimes(1);

        await wrapper.find('input').setValue('test2');
        expect(mockSetFilter).toHaveBeenCalledTimes(2);
    });
});
