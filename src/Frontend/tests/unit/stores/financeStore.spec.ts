import { ref } from 'vue';
import useApi from '@/api/api';
import Finances from '../fixtures/finances.json';
import { useFinances } from '@/stores/financesStore';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/accounting\/book\/finance\/list/.test(url) && method === 'GET') {
        return {
            response: ref(Finances),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test finance store', () => {
    const { FinancesState, fetchFinances } = useFinances();

    it('should have an empty state before fetching', () => {
        expect(FinancesState.finances).toBe(undefined);
        expect(FinancesState.error).toBe('');
        expect(FinancesState.isLoading).toBe(false);
    });

    it('should fetch data and transfer it to the state', async () => {
        await fetchFinances();
        expect(FinancesState.finances).toEqual(Finances);
        expect(FinancesState.error).toBe('');
        expect(FinancesState.isLoading).toBe(false);
    });
});
