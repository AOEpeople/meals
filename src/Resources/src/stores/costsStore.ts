import getCosts from '@/api/getCosts';
import type { DateTime } from '@/api/getDashboardData';
import { isResponseObjectOkay } from '@/api/isResponseOkay';
import type { Dictionary } from '@/types/types';
import { reactive, readonly, watch } from 'vue';
import { translateMonth } from '@/tools/localeHelper';
import postHideUser from '@/api/postHideUser';
import { type IMessage, isMessage } from '@/interfaces/IMessage';
import postSettlement from '@/api/postSettlement';
import postCashPayment from '@/api/postCashPayment';
import postConfirmSettlement from '@/api/postConfirmSettlement';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

export interface ICosts {
    columnNames: Dictionary<DateTime>;
    users: Dictionary<UserCost>;
}

interface UserCost {
    name: string;
    firstName: string;
    hidden: boolean;
    costs: Dictionary<number>;
}

interface ICostsState extends ICosts {
    error: string;
    isLoading: boolean;
}

const CostsState = reactive<ICostsState>({
    columnNames: {},
    users: {},
    error: '',
    isLoading: false
});

const { sendFlashMessage } = useFlashMessage();

watch(
    () => CostsState.error,
    () => {
        if (CostsState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: CostsState.error
            });
        }
    }
);

function isCosts(costs: ICosts): costs is ICosts {
    if (
        costs.columnNames !== null &&
        costs.columnNames !== undefined &&
        costs.users !== null &&
        costs.users !== undefined
    ) {
        const cost = Object.values(costs.users)[0];
        const column = Object.values(costs.columnNames)[0];

        return (
            cost !== null &&
            cost !== undefined &&
            typeof (cost as UserCost).name === 'string' &&
            typeof (cost as UserCost).firstName === 'string' &&
            typeof (cost as UserCost).hidden === 'boolean' &&
            (cost as UserCost).costs !== undefined &&
            (cost as UserCost).costs !== null &&
            Object.keys(cost).length === 4 &&
            typeof (column as DateTime).date === 'string' &&
            typeof (column as DateTime).timezone === 'string' &&
            typeof (column as DateTime).timezone_type === 'number'
        );
    }

    return false;
}

export function useCosts() {
    /**
     * Fetches a list of all users and their balances in the last three month, before that and the current month.
     */
    async function fetchCosts() {
        CostsState.isLoading = true;
        const { error, costs } = await getCosts();

        if (isResponseObjectOkay(error, costs, isCosts) === true) {
            CostsState.columnNames = (costs.value as ICosts).columnNames;
            CostsState.users = (costs.value as ICosts).users;
            CostsState.error = '';
        } else {
            CostsState.error = 'Error on fetching Costs';
        }
        CostsState.isLoading = false;
    }

    /**
     * Performs a request to hide the a user with a given username.
     * @param username  The username of the user to hide.
     */
    async function hideUser(username: string) {
        const { error, response } = await postHideUser(username);

        if (error.value === true || isMessage(response.value)) {
            CostsState.error = isMessage(response.value) === true ? response.value.message : 'Error on hiding user';
        } else {
            CostsState.users[username].hidden = true;
            CostsState.error = '';
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'costs.hidden'
            });
        }
    }

    /**
     * Sends a request to settle the balance of a user with a given username.
     * The HR-Department will be informed about the request and needs to confirm it.
     * @param username  The username of the user to do the settlement for.
     */
    async function sendSettlement(username: string) {
        const { error, response } = await postSettlement(username);

        if (error.value === true || isMessage(response.value)) {
            CostsState.error =
                isMessage(response.value) === true ? response.value.message : 'Error on sending settlement';
        } else {
            CostsState.error = '';
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'costs.settlementSent'
            });
        }
    }

    /**
     * Sends the amount of money a user payed in cash to the backend.
     * @param username  The username of the user that payed in cash.
     * @param amount    The amount of money the user payed in cash.
     */
    async function sendCashPayment(username: string, amount: number) {
        const { error, response } = await postCashPayment(username, amount);

        if (error.value === true || isMessage(response.value)) {
            CostsState.error =
                isMessage(response.value) === true
                    ? (response.value as IMessage).message
                    : 'Error on sending settlement';
        } else if (typeof response.value === 'number') {
            CostsState.error = '';
            CostsState.users[username].costs['total'] += response.value;
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'costs.cashPayment'
            });
        }
    }

    /**
     * The confirmation of the settlement request by the HR-Department.
     * @param hash  The hash of the settlement.
     */
    async function confirmSettlement(hash: string) {
        const { error, response } = await postConfirmSettlement(hash);

        if (error.value === true || isMessage(response.value)) {
            CostsState.error =
                isMessage(response.value) === true
                    ? (response.value as IMessage).message
                    : 'Error on confirming settlement';
            return false;
        } else {
            CostsState.error = '';
            sendFlashMessage({
                type: FlashMessageType.INFO,
                message: 'costs.settlementConfirmed'
            });
            return true;
        }
    }

    function getColumnNames(locale: string) {
        return Object.values(CostsState.columnNames).map((dateTime) => {
            return translateMonth(dateTime, locale);
        });
    }

    function getFullNameByUser(username: string) {
        const user = CostsState.users[username];
        if (user !== undefined && user !== null) {
            return `${user.firstName} ${user.name}`;
        }

        return 'undefined';
    }

    return {
        CostsState: readonly(CostsState),
        fetchCosts,
        hideUser,
        sendSettlement,
        sendCashPayment,
        confirmSettlement,
        getColumnNames,
        getFullNameByUser
    };
}
