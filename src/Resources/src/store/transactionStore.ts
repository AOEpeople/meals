import {Store} from "@/store/store";
import { useTransactionData } from "@/hooks/getTransactionData";

class TransactionStore extends Store<any> {
    protected data(): any {
        return {
            data: [{}],
            difference: 0,
            isLoading: true,
        };
    }

    async fillStore() {
        this.state.isLoading = true;
        let {transactions} = await useTransactionData();
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