import {Store} from "@/store/store";

interface Balance extends Object {
    amount: number
}

class BalanceStore extends Store<Balance> {
    protected data(): Balance {
        return {
            amount: 0
        };
    }

    updateAmount(newAmount: number) {
        this.state.amount = newAmount;
    }

    adjustAmount(adjustAmount: number) {
        this.state.amount += adjustAmount;
    }
}

export const balanceStore: BalanceStore = new BalanceStore()