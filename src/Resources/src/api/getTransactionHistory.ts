import useApi from './api';
import { type ITransactionHistory } from '@/stores/accountingStore';

/**
 * Fetches two lists of transactions per user for the past month and the current month
 */
export default async function getTransactionHistory() {
    const { error, response: transactions, request } = useApi<ITransactionHistory>('GET', 'api/accounting/book');

    await request();

    return { error, transactions };
}
