import MenuHeader from '@/components/menu/MenuHeader.vue';
import { WeekDTO } from '@/interfaces/DayDTO';
import { shallowMount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';

const testWeek: WeekDTO = {
    id: 0,
    notify: false,
    enabled: false,
    days: []
};

describe('Test MenuHeader', () => {
    it('should contain the correct text', () => {
        const wrapper = shallowMount(MenuHeader, {
            props: {
                week: testWeek,
                dateRange: [new Date('2023-07-03T12:00:00.000+02:00'), new Date('2023-07-07T12:00:00.000+02:00')],
                calendarWeek: 27
            }
        });

        expect(wrapper.find('h2').text()).toMatch('menu.header #27 (07/03 - 07/07)');
    });
});
