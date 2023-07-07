import MenuHeader from "@/components/menu/MenuHeader.vue";
import { WeekDTO } from "@/interfaces/DayDTO";
import { mount } from "@vue/test-utils";

const testWeek: WeekDTO = {
    id: 0,
    notify: false,
    enabled: false,
    days: []
}

describe('Test MenuHeader', () => {
    it('should contain the correct text', () => {
        const wrapper = mount(MenuHeader, {
            props: {
                week: testWeek,
                dateRange: ['2023-07-03T12:00:00.000+02:00', '2023-07-07T12:00:00.000+02:00'],
                calendarWeek: 27
            }
        });

        expect(wrapper.find('h2').text()).toMatch('menu.header #27 (07/03 - 07/07)');
    });
});