import { useAccounting } from '@/stores/accountingStore';
import { ref } from 'vue';
import useApi from '@/api/api';
import Transactions from '../fixtures/transactionHistory.json';

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

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

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
