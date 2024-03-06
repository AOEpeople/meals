import { Dictionary } from 'types/types';
import { reactive, watch } from 'vue';
import getTransactionHistory from '@/api/getTransactionHistory';
import { isResponseObjectOkay } from '@/api/isResponseOkay';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

export interface IUserTransaction {
    firstName: string;
    name: string;
    amount: string;
    paymethod: string | null;
}

export interface ITransactionHistory {
    lastMonth: string;
    thisMonth: string;
    usersLastMonth: Dictionary<IUserTransaction>;
    usersThisMonth: Dictionary<IUserTransaction>;
}

interface ITransactionHistoryState {
    transactions: ITransactionHistory;
    error: string;
    isLoading: boolean;
}

function isTransactionHistory(transactionHistory: ITransactionHistory): transactionHistory is ITransactionHistory {
    if (
        transactionHistory.usersLastMonth !== null &&
        transactionHistory.usersLastMonth !== undefined &&
        transactionHistory.usersThisMonth !== null &&
        transactionHistory.usersThisMonth !== undefined
    ) {
        const lastMonth = Object.values(transactionHistory.usersLastMonth)[0];
        const thisMonth = Object.values(transactionHistory.usersThisMonth)[0];

        return (
            ((lastMonth !== null &&
                lastMonth !== undefined &&
                typeof (transactionHistory as ITransactionHistory).lastMonth === 'string' &&
                isUserTransaction(lastMonth) === true) ||
                Object.values(transactionHistory.usersLastMonth).length === 0) &&
            ((thisMonth !== null &&
                thisMonth !== undefined &&
                typeof (transactionHistory as ITransactionHistory).thisMonth === 'string' &&
                isUserTransaction(thisMonth) === true) ||
                Object.values(transactionHistory.usersThisMonth).length === 0)
        );
    }

    return false;
}

function isUserTransaction(transaction: IUserTransaction): transaction is IUserTransaction {
    return (
        transaction !== null &&
        transaction !== undefined &&
        typeof transaction.amount === 'string' &&
        typeof transaction.firstName === 'string' &&
        typeof transaction.name === 'string' &&
        Object.keys(transaction).length === 4
    );
}

export function useAccounting() {
    const TransactionState = reactive<ITransactionHistoryState>({
        transactions: undefined,
        error: '',
        isLoading: false
    });

    const { sendFlashMessage } = useFlashMessage();

    watch(
        () => TransactionState.error,
        () => {
            if (TransactionState.error !== '') {
                sendFlashMessage({
                    type: FlashMessageType.ERROR,
                    message: TransactionState.error
                });
            }
        }
    );

    /**
     * Fetches the a list of all users and their transactions.
     */
    async function fetchTransactionHistory() {
        TransactionState.isLoading = true;
        const { error, transactions } = await getTransactionHistory();

        if (isResponseObjectOkay(error, transactions, isTransactionHistory) === true) {
            TransactionState.transactions = transactions.value;
            TransactionState.error = '';
        } else {
            TransactionState.error = 'Error on fetching the transaction history';
        }

        TransactionState.isLoading = false;
    }

    return {
        TransactionState,
        fetchTransactionHistory
    };
}
