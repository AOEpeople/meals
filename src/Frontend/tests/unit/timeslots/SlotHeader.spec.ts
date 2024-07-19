import { describe, expect } from '@jest/globals';
import { mount } from '@vue/test-utils';
import CreateButton from '@/components/misc/CreateButton.vue';
import SlotHeader from '@/components/timeslots/SlotHeader.vue';

describe('Test SlotHeader', () => {
    it('should have a Header with the correct i18n text', () => {
        const wrapper = mount(SlotHeader);

        expect(wrapper.find('h2').text()).toMatch(/slot.header/);
    });

    it('should have a CreateButton-component with the correct i18n text', () => {
        const wrapper = mount(SlotHeader);

        expect(wrapper.findComponent(CreateButton).text()).toMatch(/slot.create/);
    });
});
