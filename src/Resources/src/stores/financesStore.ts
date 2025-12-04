import { reactive, watch } from 'vue';
import getFinances from '@/api/getFinances';
import { type Dictionary } from '@/types/types';
import { isResponseObjectOkay } from '@/api/isResponseOkay';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

export interface Finances {
    heading: string;
    transactions: Dictionary<Transaction[]>;
}

export interface Transaction {
    firstName: string;
    name: string;
    amount: number;
    date: string;
}

interface FinancesState {
    finances: Finances[];
    isLoading: boolean;
    error: string;
}

function isFinances(finances: Finances[]): finances is Finances[] {
    const finance = finances[0];
    if (
        finance.heading !== null &&
        finance.heading !== undefined &&
        finance.transactions !== null &&
        finance.transactions !== undefined
    ) {
        const transactions = Object.values(finance.transactions)[0];
        return typeof finance.heading === 'string' && isTransactions(transactions);
    }

    return false;
}

function isTransactions(transactions: Transaction[]): transactions is Transaction[] {
    return (
        transactions &&
        transactions.some((transaction) => {
            return (
                transaction !== null &&
                transaction !== undefined &&
                typeof transaction.firstName === 'string' &&
                typeof transaction.name === 'string' &&
                typeof transaction.amount === 'number' &&
                typeof transaction.date === 'string'
            );
        })
    );
}

export function useFinances() {
    const { sendFlashMessage } = useFlashMessage();

    const FinancesState = reactive<FinancesState>({
        finances: [],
        isLoading: false,
        error: ''
    });

    watch(
        () => FinancesState.error,
        () => {
            if (FinancesState.error !== '') {
                sendFlashMessage({
                    type: FlashMessageType.ERROR,
                    message: FinancesState.error,
                    hasLifetime: true
                });
            }
        }
    );

    /**
     * Fetches a list of finances from the API and updates the FinancesState
     */
    async function fetchFinances(dateRange?: Date[]) {
        FinancesState.isLoading = true;
        const { finances, error } = await getFinances(dateRange);

        if (isResponseObjectOkay(error, finances, isFinances)) {
            FinancesState.finances = finances.value as Finances[];
            FinancesState.error = '';
        } else if (
            error.value === false &&
            finances.value !== undefined &&
            finances.value[0] !== undefined &&
            finances.value[0] !== null &&
            typeof finances.value[0].heading === 'string'
        ) {
            FinancesState.finances = finances.value;
            FinancesState.error = '';
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'finance.empty',
                hasLifetime: true
            });
        } else {
            FinancesState.finances = [];
            FinancesState.error = 'Error on fetching finances';
        }

        FinancesState.isLoading = false;
    }

    return {
        FinancesState,
        fetchFinances
    };
}
