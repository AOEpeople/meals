import { useCosts } from '@/stores/costsStore';
import useApi from '@/api/api';
import { ref } from 'vue';
import Costs from '../fixtures/getCosts.json';

const PAYMENT_AMOUNT = 100;

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const getMockedResponses = (method: string, url: string) => {
    if (/api\/costs\/hideuser\/[a-z]+.[a-z]+/.test(url) && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/costs\/settlement\/[a-z]+.[a-z]+/.test(url) && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/payment\/cash\/[a-z]+.[a-z]+\?amount=[0-9]+/.test(url) && method === 'POST') {
        return {
            response: ref(PAYMENT_AMOUNT),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/costs\/settlement\/confirm\/[a-z]+/.test(url) && method === 'POST') {
        return {
            response: ref(null),
            request: asyncFunc,
            error: ref(false)
        };
    } else if (/api\/costs/.test(url) && method === 'GET') {
        return {
            response: ref(Costs),
            request: asyncFunc,
            error: ref(false)
        };
    }
};

// @ts-expect-error ts doesn't allow reassignig a import but we need that to mock that function
// eslint-disable-next-line @typescript-eslint/no-unused-vars
useApi = jest.fn().mockImplementation((method: string, url: string) => getMockedResponses(method, url));

describe('Test CostsStore', () => {
    const { CostsState, fetchCosts, hideUser, sendCashPayment, getColumnNames, getFullNameByUser } = useCosts();

    it('should not contain data before fetching', () => {
        expect(CostsState.users).toEqual({});
        expect(CostsState.columnNames).toEqual({});
        expect(CostsState.error).toEqual('');
        expect(CostsState.isLoading).toBeFalsy();
    });

    it('should contain data after fetching', async () => {
        await fetchCosts();
        expect(CostsState.users).toEqual(Costs.users);
        expect(CostsState.columnNames).toEqual(Costs.columnNames);
        expect(CostsState.error).toEqual('');
        expect(CostsState.isLoading).toBeFalsy();
    });

    it('should hide a user and update the state', async () => {
        const userKey = Object.keys(CostsState.users)[0];
        expect(CostsState.users[userKey].hidden).toBeFalsy();
        await hideUser(userKey);
        expect(CostsState.users[userKey].hidden).toBeTruthy();
    });

    it('should send a cash payment and update the state', async () => {
        const userKey = Object.keys(CostsState.users)[0];
        const user = CostsState.users[userKey];
        const oldBalance = user.costs['total'];
        await sendCashPayment(userKey, PAYMENT_AMOUNT);
        expect(user.costs['total']).toEqual(oldBalance + PAYMENT_AMOUNT);
    });

    it('should return 3 month strings and a date string', () => {
        const columnNames = getColumnNames('de');

        expect(columnNames).toHaveLength(4);
        expect(columnNames[0]).toEqual('Mai');
        expect(columnNames[1]).toEqual('Juni');
        expect(columnNames[2]).toEqual('Juli');
        expect(columnNames[3]).toEqual('August');
    });

    it('should return the full name of a user', () => {
        const userKey = Object.keys(CostsState.users)[0];
        const fullName = getFullNameByUser(userKey);

        expect(fullName).toEqual('Admin Meals');
    });
});
