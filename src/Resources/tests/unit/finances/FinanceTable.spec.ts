import { mount } from '@vue/test-utils';
import FinanceTable from '@/components/finance/FinanceTable.vue';
import Finances from '../fixtures/finances.json';

describe('Test FinanceTable', () => {
    it('should have 4 columns', () => {
        const wrapper = mount(FinanceTable, {
            props: {
                transactions: Finances[0].transactions
            }
        });

        expect(wrapper.findAll('th')).toHaveLength(4);
    });

    it('should render 7 rows', () => {
        const wrapper = mount(FinanceTable, {
            props: {
                transactions: Finances[0].transactions
            }
        });

        expect(wrapper.findAll('tr')).toHaveLength(7);
    });
});
