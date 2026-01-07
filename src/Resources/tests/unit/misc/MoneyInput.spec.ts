import { describe, it, expect} from 'vitest';
import MoneyInput from '../../../src/components/misc/MoneyInput.vue';
import {mount} from '@vue/test-utils';
describe('Test MoneyInput', () => {
    it('should input number and convert to money', async () => {
        const wrapper = mount(MoneyInput, {
            props: {
                modelValue: 1234.567,
            },
        });

        const input = wrapper.get('input');

        expect((input.element as HTMLInputElement).value).toBe('1.234,57');

        await input.trigger('focus');
        expect((input.element as HTMLInputElement).value).toBe('1234.567');

        await input.trigger('blur');
        expect((input.element as HTMLInputElement).value).toBe('1.234,57');
    });

    it('should input number with 30 cents and convert to money', async () => {
        const wrapper = mount(MoneyInput, {
            props: {
                modelValue: 12.3,
            },
        });

        const input = wrapper.get('input');
        await input.setValue('12,3');

        expect((input.element as HTMLInputElement).value).toBe('12,3');

        await input.trigger('focus');
        expect((input.element as HTMLInputElement).value).toBe('12.3');

        await input.trigger('blur');
        expect((input.element as HTMLInputElement).value).toBe('12,30');
    });

    it('should input negative number and not convert to money', async () => {
        const wrapper = mount(MoneyInput, {
            props: {
                modelValue: 1234.5,
            },
        });

        const input = wrapper.get('input');
        await input.setValue('-1234.5');
        expect(wrapper.emitted("update:modelValue")).toBeFalsy();

        expect((input.element as HTMLInputElement).value).toBe('-1234.5');

        await input.trigger('focus');
        expect((input.element as HTMLInputElement).value).toBe('1234.5');

        await input.trigger('blur');
        expect((input.element as HTMLInputElement).value).toBe('1.234,50');
    });
});
