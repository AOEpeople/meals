import {Store} from "@/stores/store";
import { useTransactionData } from "@/api/getTransactionData";

type TransStore = {
    data: object[],
    difference: number,
    isLoading: boolean
}

class TransactionStore extends Store<TransStore> {
    protected data(): TransStore {
        return {
            data: [{}],
            difference: 0,
            isLoading: true,
        };
    }

    async fillStore() {
        this.state.isLoading = true;
        const {transactions} = await useTransactionData();
        if (transactions.value) {
            this.state.data = transactions.value.data;
            this.state.difference = transactions.value.difference;
            this.state.isLoading = false;
        } else {
            console.log('could not receive Transactions');
        }
    }
}

export const transactionStore: TransactionStore = new TransactionStore()