import SlotActions from "@/components/timeslots/SlotActions.vue";
import { describe, expect, it } from "@jest/globals";
import { computed } from "vue";
import { mount } from "@vue/test-utils";
import { TimeSlot } from "@/stores/timeSlotStore";

jest.mock("vue-i18n", () => ({
    useI18n: () => ({
        t: (key: string) => key,
        locale: computed(() => 'en')
    })
}));

const timeSlot: TimeSlot = {
    title: "TestSlot 1234",
    limit: 12,
    order: 0,
    enabled: true
}

describe('Test SlotActions', () => {
    it('should contain i18n texts', () => {
        const wrapper = mount(SlotActions, {
            props: {
                timeSlot: timeSlot,
                timeSlotId: 1
            }
        });

        expect(wrapper.findAll('p').map(ele => ele.text())).toContain('button.edit');
        expect(wrapper.findAll('p').map(ele => ele.text())).toContain('button.delete');
    });
});