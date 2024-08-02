import useApi from '@/api/api';
import postCashPayment from '@/api/postCashPayment';
import { vi, describe, it, expect } from 'vitest';
import { ref } from 'vue';

const asyncFunc: () => Promise<void> = async () => {
    new Promise((resolve) => resolve(undefined));
};

const mockedReturnValue = {
    response: ref(123),
    request: asyncFunc,
    error: ref(false)
};

vi.mock('@/api/api', () => ({
    default: vi.fn(() => { return mockedReturnValue })
}));

describe('Test postCashPayment', () => {
    it('should return the amount, that was paid', async () => {
        const { error, response } = await postCashPayment('TestUser987', 123);

        expect(useApi).toHaveBeenCalledWith('POST', 'api/payment/cash/TestUser987?amount=123');
        expect(error.value).toBe(false);
        expect(response.value).toBe(123);
    });
});
