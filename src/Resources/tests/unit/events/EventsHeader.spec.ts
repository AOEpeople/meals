import { mount } from '@vue/test-utils';
import EventsHeader from '@/components/events/EventsHeader.vue';
import CreateButton from '@/components/misc/CreateButton.vue';
import InputLabel from '@/components/misc/InputLabel.vue';
import { describe, it, expect } from 'vitest';

describe('Test EventsHeader', () => {
    it('should render the correct components and contain the correct i18n texts', () => {
        const wrapper = mount(EventsHeader, {
            props: {
                modelValue: 'initialText',
                'onUpdate:modelValue': (e) => wrapper.setProps({ modelValue: e })
            }
        });

        expect(wrapper.get('h2').text()).toMatch(/event.header/);
        expect(wrapper.get('button').text()).toMatch(/event.create/);
        expect((wrapper.get('input').element as HTMLInputElement).placeholder).toMatch(/event.search/);
        expect(wrapper.findComponent(CreateButton).exists()).toBeTruthy();
        expect(wrapper.findComponent(InputLabel).exists()).toBeTruthy();
    });
});
