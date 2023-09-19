import {reactive} from 'vue';
import getFinances from '@/api/getFinances';
import {Dictionary} from '../../types/types';
import {isResponseObjectOkay} from '@/api/isResponseOkay';

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
  if (finance.heading !== null &&
      finance.heading !== undefined &&
      finance.transactions !== null &&
      finance.transactions !== undefined
  ) {
    const transactions = Object.values(finance.transactions)[0];
    return (
        typeof finance.heading === 'string' &&
        isTransactions(transactions)
    )
  }

  return false;
}

function isTransactions(transactions: Transaction[]): transactions is Transaction[] {
  return transactions.some((transaction) => {
    return (
        transaction !== null &&
        transaction !== undefined &&
        typeof transaction.firstName === 'string' &&
        typeof transaction.name === 'string' &&
        typeof transaction.amount === 'number' &&
        typeof transaction.date === 'string'
    )
  })
}

export function useFinances() {

  const FinancesState = reactive<FinancesState>({
    finances: undefined,
    isLoading: false,
    error: ''
  });

  /**
   * Fetches a list of finances from the API and updates the FinancesState
   */
  async function fetchFinances(dateRange?: Date[]) {
    FinancesState.isLoading = true;
    const {finances, error} = await getFinances(dateRange);

    if (isResponseObjectOkay(error, finances, isFinances)) {
      FinancesState.finances = finances.value;
      FinancesState.error = '';
    } else {
      FinancesState.error = 'Error on fetching finances';
    }

    FinancesState.isLoading = false;
  }


  return {
    FinancesState,
    fetchFinances,
  }
}
