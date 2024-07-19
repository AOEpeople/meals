import SlotActions from '@/components/timeslots/SlotActions.vue';
import { describe, expect, it } from '@jest/globals';
import { mount } from '@vue/test-utils';
import { TimeSlot } from '@/stores/timeSlotStore';

const timeSlot: TimeSlot = {
    title: 'TestSlot 1234',
    limit: 12,
    order: 0,
    enabled: true
};

describe('Test SlotActions', () => {
    it('should contain i18n texts', () => {
        const wrapper = mount(SlotActions, {
            props: {
                timeSlot: timeSlot,
                timeSlotId: 1
            }
        });

        expect(wrapper.findAll('p').map((ele) => ele.text())).toContain('button.edit');
        expect(wrapper.findAll('p').map((ele) => ele.text())).toContain('button.delete');
    });
});
