import ActionButton from '@/components/misc/ActionButton.vue';
import { shallowMount } from '@vue/test-utils';
import { Action } from '@/enums/Actions';
import { describe, it, expect } from 'vitest';

describe('Test ActionButton', () => {
    it('should be a button and contain the correct text', () => {
        const wrapper = shallowMount(ActionButton, {
            props: {
                btnText: 'testText123',
                action: Action.DELETE
            }
        });

        expect(wrapper.findAll('button')).toHaveLength(1);
        expect(wrapper.find('p').text()).toMatch(/testText123/);
    });
});
