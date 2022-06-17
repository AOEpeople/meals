import {Store} from "@/store/store";
import { useTransactions } from "@/hooks/getTransactions";

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
        let { transactions } = await useTransactions();
        console.log(transactions)
        try {
            if(transactions.value){
                this.state.data = transactions.value.data;
                this.state.difference = transactions.value.difference;
                this.state.isLoading = false;
            } else {
                throw new Error('could not receive Transactions');
            }
        } catch (e) {
            console.log(e)
        }
    }
}

export const transactionStore: TransactionStore = new TransactionStore()