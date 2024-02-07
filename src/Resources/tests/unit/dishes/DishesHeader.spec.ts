import DishesHeader from '@/components/dishes/DishesHeader.vue';
import { describe, it, expect } from '@jest/globals';
import { mount } from '@vue/test-utils';
import CreateButton from '@/components/misc/CreateButton.vue';
import DishesSearchBar from '@/components/dishes/DishesSearchBar.vue';

describe('Test DishesHeader', () => {
    it('should have render with the correct i18n text', () => {
        const wrapper = mount(DishesHeader);

        expect(wrapper.find('h2').text()).toMatch(/dish.header/);
        expect(wrapper.find('button').text()).toMatch(/dish.create/);
        expect(wrapper.findComponent(CreateButton).exists()).toBe(true);
        expect(wrapper.findComponent(DishesSearchBar).exists()).toBe(true);
    });
});
