import useApi from '@/api/api';
import { ref } from 'vue';
import { DateTime } from './getDashboardData';

export type Transactions = {
    data: Transaction[];
    difference: number;
};

export type Transaction = {
    type: string;
    date: DateTime;
    timestamp: string;
    description_en: string;
    description_de: string;
    amount: number;
};

export async function useTransactionData() {
    const { response: transactions, request } = useApi<Transactions>('GET', 'api/transactions');

    const loaded = ref(false);

    if (loaded.value === false) {
        await request();
        loaded.value = true;
    }

    return { transactions };
}
