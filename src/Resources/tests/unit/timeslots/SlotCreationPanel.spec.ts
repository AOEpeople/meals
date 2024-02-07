import SlotCreationPanel from '@/components/timeslots/SlotCreationPanel.vue';
import InputLabel from '@/components/misc/InputLabel.vue';
import { describe, it } from '@jest/globals';
import { mount } from '@vue/test-utils';

describe('Test SlotCreationPanel', () => {
    it('should contain a header, three InputLbel-components and a separate input field', () => {
        const wrapper = mount(SlotCreationPanel, {
            props: {
                header: 'test',
                submit: 'submit',
                id: 123
            }
        });

        expect(wrapper.findAllComponents(InputLabel)).toHaveLength(3);
        expect(wrapper.findAll('input')).toHaveLength(4);
        expect(wrapper.findAll('label')).toHaveLength(3);
        expect(wrapper.find('h3').exists()).toBeTruthy();
    });

    it('should display the correct i18n texts', () => {
        const wrapper = mount(SlotCreationPanel, {
            props: {
                header: 'test',
                submit: 'submit',
                id: 123
            }
        });

        expect(wrapper.find('h3').text()).toMatch(/test/);
        const inputs = wrapper.findAll('input').map((ele) => ele.element.value);
        expect(inputs).toContain('slot.save');
        const labels = wrapper.findAll('label').map((ele) => ele.text());
        expect(labels).toContain('slot.slotTitle');
        expect(labels).toContain('slot.slotLimit');
        expect(labels).toContain('slot.slotOrder');
    });

    it('should have the props as placeholders', () => {
        const wrapper = mount(SlotCreationPanel, {
            props: {
                header: 'test',
                submit: 'submit',
                id: 123,
                title: 'testTitle',
                order: '789',
                limit: '456'
            }
        });

        const inputs = wrapper.findAll('input').map((ele) => ele.element.value);
        expect(inputs).toContain('testTitle');
        expect(inputs).toContain('789');
        expect(inputs).toContain('456');
    });
});
