import CostsTable from '@/components/costs/CostsTable.vue';
import Costs from '../fixtures/getCosts.json';
import { ref } from 'vue';
import { useCosts } from '@/stores/costsStore';
import { mount } from '@vue/test-utils';
import { vi, describe, beforeAll, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Costs),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test CostsTable', () => {
    const { fetchCosts } = useCosts();

    beforeAll(async () => {
        await fetchCosts();
    });

    it('should have 8 columns', () => {
        const wrapper = mount(CostsTable, {
            props: {
                filter: '',
                showHidden: true
            }
        });

        expect(wrapper.findAll('th')).toHaveLength(8);
    });

    it('should render 8 rows', () => {
        const wrapper = mount(CostsTable, {
            props: {
                filter: '',
                showHidden: true
            }
        });

        expect(wrapper.findAll('tr')).toHaveLength(8);
    });
});
