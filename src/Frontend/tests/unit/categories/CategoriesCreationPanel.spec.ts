import { describe, expect, it } from '@jest/globals';
import CategoriesCreationPanel from '@/components/categories/CategoriesCreationPanel.vue';
import { mount } from '@vue/test-utils';
import InputLabel from '@/components/misc/InputLabel.vue';
import SubmitButton from '@/components/misc/SubmitButton.vue';

describe('Test CategoriesCrearionPanel', () => {
    it('should contain a header, two InputLabel-components and a submit input', () => {
        const wrapper = mount(CategoriesCreationPanel, {
            props: {
                edit: false
            }
        });

        expect(wrapper.findAllComponents(InputLabel)).toHaveLength(2);
        expect(wrapper.findComponent(SubmitButton).exists()).toBeTruthy();
        expect(wrapper.find('h3').exists()).toBeTruthy();
    });

    it('should display the i18n texts', () => {
        const wrapper = mount(CategoriesCreationPanel, {
            props: {
                edit: false
            }
        });

        expect(wrapper.find('h3').text()).toMatch(/category.popover.create/);
        const labels = wrapper.findAll('label').map((ele) => ele.text());
        expect(labels).toContain('category.popover.german');
        expect(labels).toContain('category.popover.english');
    });

    it('should display the props as placeholders', () => {
        const wrapper = mount(CategoriesCreationPanel, {
            props: {
                titleDe: 'TestDe123',
                titleEn: 'TestEn123',
                index: 1,
                edit: true
            }
        });

        expect(wrapper.find('h3').text()).toMatch(/category.popover.edit/);
        const inputs = wrapper.findAll('input').map((ele) => ele.element.value);
        expect(inputs).toContain('TestDe123');
        expect(inputs).toContain('TestEn123');
    });
});
