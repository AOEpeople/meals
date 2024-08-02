import CashRegisterTable from '@/components/cashRegister/CashRegisterTable.vue';
import TransctionsHistory from '../fixtures/transactionHistory.json';
import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';

describe('Test CashRegisterTable', () => {
    const wrapper = mount(CashRegisterTable, {
        propsData: {
            transactions: TransctionsHistory.usersLastMonth,
            dateRange: TransctionsHistory.lastMonth
        }
    });

    it('renders the correct markup', () => {
        expect(wrapper.text()).toContain(TransctionsHistory.lastMonth);
    });

    it('has a table with 3 columns', () => {
        expect(wrapper.findAll('th').length).toEqual(3);
    });

    it('has a table with 8 rows', () => {
        expect(wrapper.findAll('tr').length).toEqual(8);
    });
});
