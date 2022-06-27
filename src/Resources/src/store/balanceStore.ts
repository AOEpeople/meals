import {Store} from "@/store/store";
import { useI18n } from "vue-i18n";

interface Balance extends Object {
    amount: number
}

class BalanceStore extends Store<Balance> {
    protected data(): Balance {
        return {
            amount: 0
        };
    }

    fillStore(): void {
        try {
            let balance = sessionStorage.getItem('balance');
            if (balance) {
                this.state.amount = parseFloat(balance);
            } else {
                throw new Error('Balance not set')
            }
        } catch (e) {
            console.log(e)
        }
    }

    updateAmount(newAmount: number): void {
        this.state.amount = newAmount;
    }

    adjustAmount(adjustAmount: number): void {
        this.state.amount += adjustAmount;
    }

    toLocalString(): string {
        let { locale } = useI18n();
        if(locale.value === 'en') {
            return this.state.amount.toFixed(2);
        } else {
            return this.state.amount.toFixed(2).replace(/\./g, ',');
        }
    }
}

export const balanceStore: BalanceStore = new BalanceStore()