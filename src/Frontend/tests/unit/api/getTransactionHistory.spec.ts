import useApi from '@/api/api';
import getTransactionHistory from '@/api/getTransactionHistory';
import { ref } from 'vue';
import Transactions from '../fixtures/transactionHistory.json';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(Transactions),
    request: asyncFunc,
    error: ref(false)
};

// @ts-expect-error ts doesn't like mocking with jest.fn()
useApi = jest.fn(useApi);
// @ts-expect-error continuation of expect error from line above
useApi.mockReturnValue(mockedReturnValue);

describe('Test getTransactionHistory', () => {
    it('should return the transaction history', async () => {
        const { error, transactions } = await getTransactionHistory();

        expect(error.value).toEqual(false);
        expect(transactions.value).toEqual(Transactions);
    });
});
