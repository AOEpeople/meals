import CostsHeader from '@/components/costs/CostsHeader.vue';
import InputLabel from '@/components/misc/InputLabel.vue';
import CashRegisterLink from '@/components/costs/CashRegisterLink.vue';
import { mount } from '@vue/test-utils';

jest.mock('epic-spinners', () => ({
    RadarSpinner: () => '<div>x</div>'
}));

jest.mock('@/stores/userDataStore', () => ({
    userDataStore: {
        roleAllowsRoute: () => true
    }
}));

describe('Test CostsHeader', () => {
    const wrapper = mount(CostsHeader, {
        props: {
            showHidden: false,
            modelValue: 'initialText',
            'onUpdate:modelValue': (e) => wrapper.setProps({ modelValue: e })
        }
    });

    it('should render correctly', () => {
        expect(wrapper.find('h2').text()).toBe('costs.header');
        expect(wrapper.findComponent(InputLabel).exists()).toBe(true);
        expect(wrapper.findComponent(CashRegisterLink).text()).toContain('costs.cashRegister');
        expect(wrapper.find('label').text()).toBe('costs.showHidden');
    });
});
