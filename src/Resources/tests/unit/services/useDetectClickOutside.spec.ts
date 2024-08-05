import { mount } from '@vue/test-utils';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import { ref } from 'vue';
import { describe, it, expect, vi } from 'vitest';

const TestComponent = {
    template: '<div><p>Test</p><span>Click</span></div>'
};

describe('Test useDetectClickOutside', () => {
    it('should call callback if click was outside of component', async () => {
        const wrapper = mount(TestComponent, {
            attachTo: document.body
        });

        expect(wrapper.text()).toContain('Test');

        const componentRef = ref(wrapper.find('p').element);
        const spy = vi.fn();

        useDetectClickOutside(componentRef, spy);
        await wrapper.find('span').trigger('click');
        expect(spy).toHaveBeenCalledTimes(1);

        useDetectClickOutside(componentRef, spy);
        await wrapper.find('p').trigger('click');
        expect(spy).toHaveBeenCalledTimes(1);
    });
});
