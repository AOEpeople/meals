import getTransactionHistory from '@/api/getTransactionHistory';
import { ref } from 'vue';
import Transactions from '../fixtures/transactionHistory.json';
import { vi, describe, it, expect } from 'vitest';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Transactions),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test getTransactionHistory', () => {
    it('should return the transaction history', async () => {
        const { error, transactions } = await getTransactionHistory();

        expect(error.value).toEqual(false);
        expect(transactions.value).toEqual(Transactions);
    });
});
