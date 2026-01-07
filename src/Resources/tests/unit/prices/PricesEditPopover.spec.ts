import {mount} from '@vue/test-utils';
import {beforeEach, describe, expect, it, vi} from 'vitest';
import {reactive} from 'vue';
import PricesEditPopover from '@/components/prices/PricesEditPopover.vue';
import {PriceUpdateData} from '../../../src/api/putUpdatePrice';

const updatePriceMock = vi.fn();

const PricesStateMock = reactive({
    prices: {
        2024: { price: 4.2, price_combined: 6.2, year: 2024 },
        2025: { price: 4.4, price_combined: 6.4, year: 2025 },
        2026: { price: 4.6, price_combined: 6.6, year: 2026 },
    } as Record<number, PriceUpdateData>,
});

const priceByYear = { price: 4.4, price_combined: 6.4, year: 2025 };
vi.mock('@/stores/pricesStore', () => ({
    usePrices: () => ({
        PricesState: PricesStateMock,
        getPriceByYear: () => priceByYear
    }),
}));

const InputLabelStub = {
    name: 'InputLabel',
    props: {
        modelValue: String,
        labelText: String,
        required: Boolean,
        min: Number,
        max: Number,
        type: String,
        step: String,
        error: String,
    },
    emits: ['update:modelValue'],
    template: `
    <div>
      <input
        data-testid="number-input"
        :value="modelValue"
        :min="min"
        :max="max"
        @input="$emit('update:modelValue', $event.target.value)"
      />
      <p v-if="error" data-testid="field-error">{{ error }}</p>
    </div>
  `,
};

const SubmitButtonStub = {
    name: 'SubmitButton',
    props: { disabled: Boolean },
    template: `<button data-testid="submit-btn" type="submit">submit</button>`,
};

describe('Test PricesCreationPanel', () => {
    let wrapper;
    beforeEach(() => {
        vi.clearAllMocks();
        updatePriceMock.mockResolvedValue(true);
        PricesStateMock.prices = {
            2024: { price: 4.2, price_combined: 6.2, year: 2024 },
            2025: { price: 4.4, price_combined: 6.4, year: 2025 },
            2026: { price: 4.6, price_combined: 6.6, year: 2026 },
        };

        wrapper = mount(PricesEditPopover, {
            props: {
                year: 2025
            },
            global: {
                stubs: {
                    InputLabel: InputLabelStub,
                    SubmitButton: SubmitButtonStub,
                },
            },
        });
    });

    it('should show validation error due price is below 0', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        expect(inputs).toHaveLength(2);

        expect((inputs[0].element as HTMLInputElement).value).toBe('4.40');
        expect((inputs[1].element as HTMLInputElement).value).toBe('6.40');

        await inputs[0].setValue('');
        await wrapper.find('form').trigger('submit.prevent');

        expect(wrapper.text()).toContain('prices.errors.invalidPrice');
    });

    it('should submit form successfully', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        await inputs[0].setValue('10.5');
        await inputs[1].setValue('12.25');

        await wrapper.find('form').trigger('submit.prevent');
        const isEventTriggered = wrapper.emitted('update');
        expect(isEventTriggered).toBeTruthy();
        expect(isEventTriggered![0][0]).toEqual({
            year: 2025,
            price: 10.5,
            price_combined: 12.25,
        });
    });

    it('should show error when update emit throws error', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        await inputs[0].setValue('10.5');
        await inputs[1].setValue('12.25');
        const emitSpy = vi
            .spyOn((wrapper.vm).$, 'emit')
            .mockImplementation((event: string) => {
                if (event === 'update') {
                    throw new Error('emit failed');
                }
                return undefined;
            });

        await wrapper.find('form').trigger('submit.prevent');

        expect(wrapper.text()).toContain('prices.errors.updateFailed');
        expect(emitSpy).toHaveBeenCalledWith('update', {
            year: 2025,
            price: 10.5,
            price_combined: 12.25,
        });
        expect(wrapper.emitted('update')).toBeFalsy();
    });

    it ('should get min and max prices for input fields', () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        expect(inputs).toHaveLength(2);
        const priceInput = inputs[0].element as HTMLInputElement;
        const combinedInput = inputs[1].element as HTMLInputElement;

        expect(Number(priceInput.min)).toBe(4.2);
        expect(Number(priceInput.max)).toBe(4.6);
        expect(Number(combinedInput.min)).toBe(6.2);
        expect(Number(combinedInput.max)).toBe(6.6);
    });
});