import DishActions from '@/components/dishes/DishActions.vue';
import { mount } from '@vue/test-utils';
import Dishes from '../fixtures/getDishes.json';
import ActionButton from '@/components/misc/ActionButton.vue';
import { Action } from '@/enums/Actions';
import { vi, describe, it, expect } from 'vitest';

const mockDeleteDishWithSlug = vi.fn();

vi.mock('@/stores/dishesStore', () => ({
    useDishes: () => ({
        deleteDishWithSlug: mockDeleteDishWithSlug
    })
}));

describe('Test DishActions', () => {
    it('should contain all action buttons', () => {
        const actions = [Action.EDIT, Action.DELETE, Action.CREATE];

        const wrapper = mount(DishActions, {
            props: {
                dish: Dishes[0],
                index: 0
            }
        });

        const actionButtons = wrapper.findAllComponents(ActionButton);
        expect(actionButtons).toHaveLength(3);

        actionButtons.forEach((actionButton) => {
            expect(actions).toContain(actionButton.props('action'));
        });
    });

    it('should call deleteDishWithSlug when clicking on delete', async () => {
        const wrapper = mount(DishActions, {
            props: {
                dish: Dishes[0],
                index: 0
            }
        });

        const actionButtons = wrapper.findAllComponents(ActionButton);

        for (const actionButton of actionButtons) {
            if (actionButton.props('action') === 'DELETE') {
                await actionButton.trigger('click');
            }
        }

        expect(mockDeleteDishWithSlug).toHaveBeenCalled();
    });
});
