import { mount } from '@vue/test-utils';
import ErrorTrafficLight from '@/components/navbar/ErrorTrafficLight.vue';
import { describe, it, expect } from 'vitest';

describe('Test ErrorTrafficLight', () => {
    it('renders RefreshIcon if no errors are in props', () => {
        const wrapper = mount(ErrorTrafficLight, {
            props: {
                errorStates: [false, false]
            }
        });

        expect(wrapper.find('.text-green').exists()).toBe(true);
        expect(wrapper.find('.text-red').exists()).toBe(false);
    });

    it('renders ExclemationCircleIcon if errors are in props', () => {
        const wrapper = mount(ErrorTrafficLight, {
            props: {
                errorStates: [true, false]
            }
        });

        expect(wrapper.find('.text-green').exists()).toBe(false);
        expect(wrapper.find('.text-red').exists()).toBe(true);
    });
});
