import getPrices from '@/api/getPrices';
import postCreatePrice from '@/api/postCreatePrice';
import putUpdatePrice from '@/api/putUpdatePrice';
import postDeletePrice from '@/api/postDeletePrice';
import { isResponseObjectOkay } from '@/api/isResponseOkay';
import type { Dictionary } from '@/types/types';
import { reactive, readonly, watch } from 'vue';
import useFlashMessage from '@/services/useFlashMessage';
import { FlashMessageType } from '@/enums/FlashMessage';

export interface IPrices {
    prices: Dictionary<PriceData>;
}

interface PriceData {
    year: number;
    price: number;
    price_combined: number;
}

interface IPricesState extends IPrices {
    error: string;
    isLoading: boolean;
}

const PricesState = reactive<IPricesState>({
    prices: {},
    error: '',
    isLoading: false
});

const { sendFlashMessage } = useFlashMessage();

watch(
    () => PricesState.error,
    () => {
        if (PricesState.error !== '') {
            sendFlashMessage({
                type: FlashMessageType.ERROR,
                message: PricesState.error,
                hasLifetime: true
            });
        }
    }
);

function isPrices(prices: IPrices): prices is IPrices {
    if (prices.prices !== null && prices.prices !== undefined) {
        const price = Object.values(prices.prices)[0];

        return (
            price !== null &&
            price !== undefined &&
            typeof (price as PriceData).year === 'number' &&
            typeof (price as PriceData).price === 'number' &&
            typeof (price as PriceData).price_combined === 'number' &&
            Object.keys(price).length === 3
        );
    }

    return false;
}

export function usePrices() {
    async function fetchPrices() {
        PricesState.isLoading = true;
        const { error, prices } = await getPrices();

        if (isResponseObjectOkay(error, prices, isPrices) === true) {
            PricesState.prices = (prices.value as IPrices).prices;
            PricesState.error = '';
        } else {
            PricesState.error = 'Error on fetching Prices';
        }
        PricesState.isLoading = false;
    }

    function getPriceByYear(year: number): PriceData | undefined {
        return Object.values(PricesState.prices).find((price) => price.year === year);
    }

    function getYears(): number[] {
        return Object.values(PricesState.prices).map((price) => price.year);
    }

    async function createPrice(data: { year: number; price: number; price_combined: number }) {
        PricesState.isLoading = true;
        const { error, response } = await postCreatePrice(data);

        if (isResponseObjectOkay(error, response) === true) {
            await fetchPrices();
            PricesState.error = '';
            PricesState.isLoading = false;
            return true;
        } else {
            PricesState.error = response.value?.message || 'Error on creating Price';
            PricesState.isLoading = false;
            return false;
        }
    }

    async function updatePrice(data: { year: number; price: number; price_combined: number }) {
        PricesState.isLoading = true;
        const { error, response } = await putUpdatePrice(data);

        if (isResponseObjectOkay(error, response) === true) {
            await fetchPrices();
            PricesState.error = '';
            PricesState.isLoading = false;
            return true;
        } else {
            PricesState.error = response.value?.message || 'Error on updating Price';
            PricesState.isLoading = false;
            return false;
        }
    }

    async function deletePrice(year: number) {
        PricesState.isLoading = true;
        const { error, response } = await postDeletePrice({ year });

        if (isResponseObjectOkay(error, response) === true) {
            await fetchPrices();
            PricesState.error = '';
            PricesState.isLoading = false;
            return true;
        } else {
            PricesState.error = response.value?.message || 'Error on deleting Price';
            PricesState.isLoading = false;
            return false;
        }
    }

    return {
        PricesState: readonly(PricesState),
        fetchPrices,
        getPriceByYear,
        getYears,
        createPrice,
        updatePrice,
        deletePrice
    };
}
