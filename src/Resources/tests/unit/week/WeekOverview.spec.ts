import WeekOverview from '@/components/weeks/WeekOverview.vue';
import { Week } from '@/stores/weeksStore';
import { mount } from '@vue/test-utils';

const weekOne: Week = {
    id: 57,
    year: 2023,
    calendarWeek: 27,
    days: {},
    enabled: true
};

const weekTwo: Week = {
    id: null,
    year: 0,
    calendarWeek: 0,
    days: {},
    enabled: false
};

describe('Test WeekOverview', () => {
    it('should render the PlusCircleIcon if week is null', () => {
        const wrapper = mount(WeekOverview, {
            props: {
                week: weekTwo
            }
        });

        expect(wrapper.find('.invisible').exists()).toBe(true);
    });

    it('should not render the PlusCircleIcon if week is not null', () => {
        const wrapper = mount(WeekOverview, {
            props: {
                week: weekOne
            }
        });

        expect(wrapper.find('.invisible').exists()).toBe(false);
    });

    it('should contain the correct week number', () => {
        const wrapper = mount(WeekOverview, {
            props: {
                week: weekOne
            }
        });

        expect(wrapper.find('h4').text()).toMatch(/menu.week #27/);
    });

    it('should contain the correct daterange', () => {
        const wrapper = mount(WeekOverview, {
            props: {
                week: weekOne
            }
        });

        expect(wrapper.find('h5').text()).toMatch(/7\/3 - 7\/7/);
    });
});
