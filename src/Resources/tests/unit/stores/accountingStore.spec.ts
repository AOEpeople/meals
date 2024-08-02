import { useAccounting } from '@/stores/accountingStore';
import { ref } from 'vue';
import Transactions from '../fixtures/transactionHistory.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/accounting\/book/.test(url) && method === 'GET') {
        return {
            response: ref(Transactions),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test accounting store', () => {
    const { TransactionState, fetchTransactionHistory } = useAccounting();

    it('should have an empty state before fetching', () => {
        expect(TransactionState.transactions).toBe(undefined);
        expect(TransactionState.error).toBe('');
        expect(TransactionState.isLoading).toBe(false);
    });

    it('should fetch data and transfer it to the state', async () => {
        await fetchTransactionHistory();
        expect(TransactionState.transactions).toEqual(Transactions);
        expect(TransactionState.error).toBe('');
        expect(TransactionState.isLoading).toBe(false);
    });
});
