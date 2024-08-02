import { ref } from 'vue';
import Finances from '../fixtures/finances.json';
import { useFinances } from '@/stores/financesStore';
import { vi, describe, it, expect } from 'vitest';

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

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test finance store', () => {
    const { FinancesState, fetchFinances } = useFinances();

    it('should have an empty state before fetching', () => {
        expect(FinancesState.finances).toEqual([]);
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
