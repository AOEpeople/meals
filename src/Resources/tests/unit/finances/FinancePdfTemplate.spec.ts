import FinancePdfTemplate from '@/components/finance/FinancePdfTemplate.vue';
import FinancesFixture from '../fixtures/finances.json';
import FinanceTable from '@/components/finance/FinanceTable.vue';
import { mount } from '@vue/test-utils';
import { Finances, useFinances } from '@/stores/financesStore';
import useApi from '@/api/api';
import { ref } from 'vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(FinancesFixture),
    request: asyncFunc,
    error: ref(false)
};

jest.mock('@/api/api');
// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi.mockImplementation(() => mockedReturnValue);

describe('Test FinancePdfTemplate', () => {
    it('should not render anything when finances.transactions are not defined', () => {
        const financeEmpty: Finances = {
            heading: 'Test1234',
            transactions: undefined
        };

        const wrapper = mount(FinancePdfTemplate, {
            props: {
                finances: financeEmpty
            }
        });

        expect(wrapper.find('h1').exists()).toBeFalsy();
    });

    it('should not render anything when finances is null', () => {
        const financeNull: Finances = null;

        const wrapper = mount(FinancePdfTemplate, {
            props: {
                finances: financeNull
            }
        });

        expect(wrapper.find('h1').exists()).toBeFalsy();
    });

    it('should render a heading and a table', async () => {
        const { FinancesState, fetchFinances } = useFinances();

        await fetchFinances();

        const wrapper = mount(FinancePdfTemplate, {
            props: {
                finances: FinancesState.finances[0]
            }
        });

        expect(wrapper.find('h1').exists()).toBeTruthy();
        expect(wrapper.find('h1').text()).toEqual('01.08. - 31.08.2023');
        expect(wrapper.findComponent(FinanceTable).exists()).toBeTruthy();
    });
});
