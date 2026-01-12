import { describe, it, expect, vi } from 'vitest';
import {ref} from "vue";
import getPricesResponse from '../fixtures/getPricesResponse.json';
import updatePriceResponse from '../fixtures/priceUpdateResponse.json';
import deletePriceResponse from '../fixtures/priceDeleteResponse.json';
import createPriceResponse from '../fixtures/createPriceResponse.json';
import {usePrices} from "../../../src/stores/pricesStore";
import {PriceCreateData} from "../../../src/api/postCreatePrice";


const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

let throwError = false;
const getMockedResponses = (method: string, url: string) => {
    if (/api\/prices/.test(url) === true && method === 'GET') {
        return {
            response: ref(getPricesResponse),
            request: asyncFunc,
            error: ref(throwError)
        };
    } else if (/api\/price\/\d+$/.test(url) === true && method === 'PUT') {
        return {
            response: ref(updatePriceResponse),
            request: asyncFunc,
            error: ref(throwError)
        };
    } else if (/api\/price\/\d+$/.test(url) === true && method === 'DELETE') {
        return {
            response: ref(deletePriceResponse),
            request: asyncFunc,
            error: ref(throwError)
        };
    } else if (/api\/price/.test(url) === true && method === 'POST') {
        return {
            response: ref(createPriceResponse),
            request: asyncFunc,
            error: ref(throwError)
        };
    }
};

vi.mock('@/api/api', () => ({
    default: vi.fn((method: string, url: string) => getMockedResponses(method, url))
}));

describe('Test pricesStore', () => {
    const {
        createPrice,
        fetchPrices,
        getPriceByYear,
        getYears,
        PricesState,
        updatePrice,
        deletePrice
    } = usePrices();

    it('should call fetchPrices', async () => {
        throwError = false;
        await fetchPrices();

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('');
    });

    it('should call fetchPrices with error', async () => {
        throwError = true;
        await fetchPrices();

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('loadPricesFailed');
    });


    it('should call fetchPrices and getPriceByYear with input 2025', async () => {
        throwError = false;
        await fetchPrices();
        const price = getPriceByYear(2025);

        const expectedPrice = {
            price: 4.4,
            price_combined: 6.4,
            year: 2025,
        };
        expect(price).toEqual(expectedPrice);
        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('');
    });

    it('should call fetchPrices and getYears', async () => {
        throwError = false;
        await fetchPrices();
        const years = getYears();

        const expectedYears = [
            2025,
            2026,
        ]
        expect(years).toEqual(expectedYears);
        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('');
    });

    it('should call createPrice', async () => {
        throwError = false;
        const priceCreateData: PriceCreateData = {
            year: 2025,
            price: 4.4,
            price_combined: 6.4
        }
        await createPrice(priceCreateData);

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('');
    });

    it('should call createPrice with error', async () => {
        throwError = true;
        const priceCreateData: PriceCreateData = {
            year: 2025,
            price: 4.4,
            price_combined: 6.4
        }
        await createPrice(priceCreateData);

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('createPriceFailed');
    });

    it('should call fetchPrices and updatePrice for year 2025', async () => {
        throwError = false;
        await fetchPrices();
        const priceCreateData: PriceCreateData = {
            year: 2025,
            price: 4.6,
            price_combined: 6.6
        }
        await updatePrice(priceCreateData);

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('');
    });

    it('should call fetchPrices and updatePrice for year 2024 with error', async () => {
        throwError = true;
        await fetchPrices();
        const priceCreateData: PriceCreateData = {
            year: 2024,
            price: 4.6,
            price_combined: 6.6
        }
        await updatePrice(priceCreateData);

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('updatePriceFailed');
    });

    it('should call fetchPrices and deletePrice for year 2026', async () => {
        throwError = false;
        await fetchPrices();
        await deletePrice(2026);

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('');
    });

    it('should call fetchPrices and deletePrice for year 2025 with error', async () => {
        throwError = true;
        await fetchPrices();
        await deletePrice(2025);

        expect(PricesState.isLoading).toBeFalsy();
        expect(PricesState.prices).toEqual(getPricesResponse.prices);
        expect(PricesState.error).toEqual('deletePriceFailed');
    });
});