import EnableWeek from '@/components/menu/EnableWeek.vue';
import { mount } from '@vue/test-utils';
import { WeekDTO } from '@/interfaces/DayDTO';
import { Ref, ref } from 'vue';
import { describe, it, expect } from 'vitest';

const testWeek: Ref<WeekDTO> = ref({
    id: 0,
    notify: false,
    enabled: false,
    days: []
});

describe('Test EnableWeek', () => {
    it('should contain the correct text', () => {
        const wrapper = mount(EnableWeek, {
            props: {
                week: testWeek.value
            }
        });

        expect(wrapper.find('span').text()).toMatch(/menu.enableWeek/);
    });
});
