import CategoriesHeader from "@/components/categories/CategoriesHeader.vue";
import { describe, expect } from "@jest/globals";
import { mount } from "@vue/test-utils";
import { computed } from "vue";
import CreateButton from "@/components/misc/CreateButton.vue";

jest.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

describe('Test CategoriesHeader', () => {
    it('should have a Header with the correct i18n text', () => {
        const wrapper = mount(CategoriesHeader);

        expect(wrapper.find('h2').text()).toMatch(/category.header/);
    });

    it('should have a CreateButton-component with the correct i18n text', () => {
        const wrapper = mount(CategoriesHeader);

        expect(wrapper.findComponent(CreateButton).text()).toMatch(/category.create/);
    });
});