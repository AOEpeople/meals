import DishActions from '@/components/dishes/DishActions.vue';
import { mount } from '@vue/test-utils';
import Dishes from '../fixtures/getDishes.json';
import ActionButton from '@/components/misc/ActionButton.vue';
import { describe, it, expect } from '@jest/globals';
import { Action } from '@/enums/Actions';

const mockDeleteDishWithSlug = jest.fn();

jest.mock('@/stores/dishesStore', () => ({
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
