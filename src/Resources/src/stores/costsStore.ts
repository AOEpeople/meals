import getCosts from "@/api/getCosts";
import { DateTime } from "@/api/getDashboardData";
import { isResponseObjectOkay } from "@/api/isResponseOkay";
import { Dictionary } from "types/types";
import { reactive, readonly } from "vue";
import { translateMonth } from "@/tools/localeHelper";

export interface ICosts {
    columnNames: Dictionary<DateTime>,
    users: Dictionary<UserCost>
}

interface UserCost {
    name: string,
    firstName: string,
    hidden: boolean,
    costs: Dictionary<number>
}

interface ICostsState extends ICosts {
    error: string,
    isLoading: boolean
}

const CostsState = reactive<ICostsState>({
    columnNames: {},
    users: {},
    error: "",
    isLoading: false
});

function isCosts(costs: ICosts): costs is ICosts {

    if (costs.columnNames !== null && costs.columnNames !== undefined && costs.users !== null && costs.users !== undefined) {
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

    async function fetchCosts() {
        CostsState.isLoading = true;
        const { error, costs } = await getCosts();

        if (isResponseObjectOkay(error, costs, isCosts) === true) {
            CostsState.columnNames = costs.value.columnNames;
            CostsState.users = costs.value.users;
            CostsState.error = '';
        } else {
            CostsState.error = 'Error on fetching Costs';
        }
        CostsState.isLoading = false;
    }

    function getColumnNames(locale: string) {
        return Object.values(CostsState.columnNames).map(dateTime => {
            return translateMonth(dateTime, locale);
        });
    }

    return {
        CostsState: readonly(CostsState),
        fetchCosts,
        getColumnNames
    }
}