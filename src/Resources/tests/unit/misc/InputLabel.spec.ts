import InputLabel from '@/components/misc/InputLabel.vue';
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import { ref } from 'vue';

describe('Test InputLabel', () => {
    it('should contain the labelText and modelValue from the props', () => {
        const wrapper = mount(InputLabel, {
            props: {
                labelText: 'TestText 1234',
                modelValue: 'TestValue'
            }
        });

        expect(wrapper.find('label').text()).toMatch(/TestText 1234/);
        expect(wrapper.find('input').element.value).toMatch(/TestValue/);
    });

    it('should emit an update event when input changes', async () => {
        const inputValue = ref('TestText');
        const wrapper = mount(InputLabel, {
            props: {
                labelText: 'TestText 1234',
                modelValue: inputValue.value
            }
        });

        expect(wrapper.find('input').element.value).toMatch(/TestText/);
        await wrapper.find('input').setValue('ABCD1234');
        expect(wrapper.find('input').element.value).toMatch(/ABCD1234/);

        expect(wrapper.emitted()).toHaveProperty('update:modelValue');
        expect(wrapper.emitted()['update:modelValue'][0]).toEqual(['ABCD1234']);
    });
});
