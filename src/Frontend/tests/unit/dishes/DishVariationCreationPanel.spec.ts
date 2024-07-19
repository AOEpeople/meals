import DishVariationCreationPanel from '@/components/dishes/DishVariationCreationPanel.vue';
import { mount } from '@vue/test-utils';
import { describe, it, expect } from '@jest/globals';

const mockCreateDishVariation = jest.fn();
const mockUpdateDishVariation = jest.fn();

jest.mock('@/stores/dishesStore', () => ({
    useDishes: () => ({
        createDishVariation: mockCreateDishVariation,
        updateDishVariation: mockUpdateDishVariation
    })
}));

describe('Test DishVariationCreationPanel', () => {
    it('should not call createDishVariation or updateDishVariation without titles or slug props', async () => {
        const wrapper = mount(DishVariationCreationPanel, {
            props: {
                parentSlug: 'test'
            }
        });

        await wrapper.find('form').trigger('submit.prevent');
        expect(mockCreateDishVariation).not.toHaveBeenCalled();
        expect(mockUpdateDishVariation).not.toHaveBeenCalled();

        await wrapper.setProps({ titleDe: 'test', titleEn: 'test' });

        await wrapper.find('form').trigger('submit.prevent');
        expect(mockCreateDishVariation).not.toHaveBeenCalled();
        expect(mockUpdateDishVariation).not.toHaveBeenCalled();
    });

    it('should call createDishVariation when submitting with title', async () => {
        const wrapper = mount(DishVariationCreationPanel, {
            props: {
                parentSlug: 'test',
                slug: 'testvar',
                titleDe: 'Test',
                titleEn: 'Test'
            }
        });

        await wrapper.find('form').trigger('submit.prevent');
        expect(mockCreateDishVariation).toHaveBeenCalled();
        expect(mockUpdateDishVariation).not.toHaveBeenCalled();
    });

    it('should call updateDishVariation when submitting with titles and edit set to true', async () => {
        const wrapper = mount(DishVariationCreationPanel, {
            props: {
                parentSlug: 'test',
                slug: 'testvar',
                titleDe: 'Test',
                titleEn: 'Test',
                edit: true
            }
        });

        await wrapper.find('form').trigger('submit.prevent');
        expect(mockUpdateDishVariation).toHaveBeenCalled();
    });
});
