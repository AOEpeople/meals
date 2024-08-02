import CostsTableActions from '@/components/costs/CostsTableActions.vue';
import ActionButton from '@/components/misc/ActionButton.vue';
import CostsActionSettlement from '@/components/costs/CostsActionSettlement.vue';
import { mount } from '@vue/test-utils';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc1 = vi.fn(async () => {
    new Promise((resolve) => resolve(undefined));
});

vi.mock('@/stores/costsStore', () => ({
    useCosts: () => ({
        hideUser: asyncFunc1
    })
}));

describe('Test CostsTableActions', () => {
    const wrapper = mount(CostsTableActions, {
        props: {
            username: 'TestUser123',
            balance: 987
        }
    });

    it('should find 3 ActionButtons', () => {
        expect(wrapper.findAllComponents(ActionButton)).toHaveLength(3);
    });

    it('should find the CostsActionSettlement', () => {
        expect(wrapper.findComponent(CostsActionSettlement).exists()).toBe(true);
    });

    it('should call hideUser on click', async () => {
        await wrapper.findAllComponents(ActionButton)[0].trigger('click');

        expect(asyncFunc1).toHaveBeenCalled();
    });
});
