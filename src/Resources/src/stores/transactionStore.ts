import { Store } from '@/stores/store';
import { type Transaction, useTransactionData } from '@/api/getTransactionData';

type TransStore = {
    data: Transaction[];
    difference: number;
    isLoading: boolean;
};

class TransactionStore extends Store<TransStore> {
    protected data(): TransStore {
        return {
            data: [{} as Transaction],
            difference: 0,
            isLoading: true
        };
    }

    async fillStore() {
        this.state.isLoading = true;
        const { transactions } = await useTransactionData();
        if (transactions.value !== undefined && transactions.value !== null) {
            this.state.data = transactions.value.data;
            this.state.difference = transactions.value.difference;
            this.state.isLoading = false;
        }
    }
}

export const transactionStore: TransactionStore = new TransactionStore();
