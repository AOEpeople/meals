import DishVariationActions from '@/components/dishes/DishVariationActions.vue';
import { Action } from '@/enums/Actions';
import ActionButton from '@/components/misc/ActionButton.vue';
import { mount } from '@vue/test-utils';
import Dishes from '../fixtures/getDishes.json';
import { vi, describe, it, expect } from 'vitest';

const mockDeleteDishVariationWithSlug = vi.fn();

vi.mock('@/stores/dishesStore', () => ({
    useDishes: () => ({
        deleteDishVariationWithSlug: mockDeleteDishVariationWithSlug
    })
}));

describe('Test DishVariationActions', () => {
    it('should contain all action buttons', () => {
        const actions = [Action.EDIT, Action.DELETE];

        const wrapper = mount(DishVariationActions, {
            props: {
                variation: Dishes[0].variations[0],
                parentSlug: Dishes[0].slug
            }
        });

        const actionButtons = wrapper.findAllComponents(ActionButton);
        expect(actionButtons).toHaveLength(2);

        actionButtons.forEach((actionButton) => {
            expect(actions).toContain(actionButton.props('action'));
        });
    });

    it('should call deleteDishWithSlug when clicking on delete', async () => {
        const wrapper = mount(DishVariationActions, {
            props: {
                variation: Dishes[0].variations[0],
                parentSlug: Dishes[0].slug
            }
        });

        const actionButtons = wrapper.findAllComponents(ActionButton);

        for (const actionButton of actionButtons) {
            if (actionButton.props('action') === 'DELETE') {
                await actionButton.trigger('click');
            }
        }

        expect(mockDeleteDishVariationWithSlug).toHaveBeenCalled();
    });
});
