import SubmitButton from "@/components/misc/SubmitButton.vue";
import { describe, expect, it } from "@jest/globals";
import { mount } from "@vue/test-utils";
import { computed } from "vue";

jest.mock('vue-i18n', () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

describe('Test SubmitButton', () => {
    it('should contain an input with an i18n-text', () => {
        const wrapper = mount(SubmitButton);

        expect(wrapper.find('input').element.value).toMatch(/slot.save/);
    });

    it('should switch classes on a mousedown/mouseup event', async () => {
        const wrapper = mount(SubmitButton);

        expect(wrapper.classes()).not.toContain('translate-y-0.5');
        expect(wrapper.classes()).toContain('shadow-btn');
        expect(wrapper.classes()).toContain('btn-highlight-shadow');

        await wrapper.trigger('mousedown');

        expect(wrapper.classes()).toContain('translate-y-0.5');
        expect(wrapper.classes()).not.toContain('shadow-btn');
        expect(wrapper.classes()).not.toContain('btn-highlight-shadow');

        await wrapper.trigger('mouseup');
        expect(wrapper.classes()).not.toContain('translate-y-0.5');
        expect(wrapper.classes()).toContain('shadow-btn');
        expect(wrapper.classes()).toContain('btn-highlight-shadow');
    });
});