import CategoriesActions from "@/components/categories/CategoriesActions.vue";
import { describe } from "@jest/globals";
import { computed } from "vue";
import { mount } from "@vue/test-utils";
import Categories from "../fixtures/getCategories.json";

jest.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

describe('Test CategoriesActions Component', () => {
    it('should contain two buttons with i18n texts', () => {
        const wrapper = mount(CategoriesActions, {
            props: {
                index: 0,
                category: Categories[0]
            }
        });

        expect(wrapper.findAll('p').map(ele => ele.text())).toContain('button.edit');
        expect(wrapper.findAll('p').map(ele => ele.text())).toContain('button.delete');
    });
})