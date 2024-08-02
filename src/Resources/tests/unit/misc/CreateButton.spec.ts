import CreateButton from '@/components/misc/CreateButton.vue';
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';

describe('Test CreateButton', () => {
    it('should display the text from the props', () => {
        const wrapper = mount(CreateButton, {
            props: {
                btnText: 'TestText123',
                open: false
            }
        });

        expect(wrapper.find('div').text()).toEqual('TestText123');
        expect(wrapper.classes()).toContain('btn-highlight-shadow');
        expect(wrapper.classes()).toContain('shadow-btn');
    });

    it('should change classes when the open prop changes', async () => {
        const wrapper = mount(CreateButton, {
            props: {
                btnText: 'Test',
                open: false
            }
        });

        expect(wrapper.classes()).toContain('btn-highlight-shadow');
        expect(wrapper.classes()).toContain('shadow-btn');

        await wrapper.setProps({ open: true });

        expect(wrapper.vm.open).toBeTruthy();
        expect(wrapper.classes()).toContain('translate-y-0.5');
        expect(wrapper.classes()).not.toContain('btn-highlight-shadow');
        expect(wrapper.classes()).not.toContain('shadow-btn');
    });
});
