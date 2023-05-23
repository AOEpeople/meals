import ActionButton from "@/components/misc/ActionButton.vue";
import { describe, it } from "@jest/globals";
import { shallowMount, mount } from "@vue/test-utils";
import { Action } from "@/enums/Actions";
import { XIcon, PencilIcon } from '@heroicons/vue/outline';

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