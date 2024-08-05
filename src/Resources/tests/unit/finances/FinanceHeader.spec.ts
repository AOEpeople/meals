import { mount } from '@vue/test-utils';
import FinanceHeader from '@/components/finance/FinanceHeader.vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import { describe, expect, it, vi } from 'vitest';

vi.mock('epic-spinners', () => ({
    RadarSpinner: () => '<div>x</div>'
}));

describe('Test FinanceHeader (with controls)', () => {
    const wrapper = mount(FinanceHeader, {
        props: {
            dateRange: '01.09.-10.09.2023',
            showControls: true
        }
    });

    it('should render correctly', () => {
        expect(wrapper.find('h2').text()).toBe('01.09.-10.09.2023');
        expect(wrapper.findComponent(VueDatePicker).exists()).toBe(true);
    });
});

describe('Test FinanceHeader (without controls)', () => {
    const wrapper = mount(FinanceHeader, {
        props: {
            dateRange: '01.09.-10.09.2023',
            showControls: false
        }
    });

    it('should render correctly', () => {
        expect(wrapper.find('h2').text()).toBe('01.09.-10.09.2023');
        expect(wrapper.findComponent(VueDatePicker).exists()).toBe(false);
    });
});
