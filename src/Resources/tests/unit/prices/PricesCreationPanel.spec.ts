import {mount} from '@vue/test-utils';
import {beforeEach, describe, expect, it, vi} from 'vitest';
import {reactive} from 'vue';
import PricesCreationPanel from '@/components/prices/PricesCreationPanel.vue';

const createPriceMock = vi.fn();

const PricesStateMock = reactive({
    prices: {
        2025: { price: 4.4, price_combined: 6.4, year: 2025 },
        2026: { price: 4.6, price_combined: 6.6, year: 2026 },
    } as Record<number, any>,
});

vi.mock('@/stores/pricesStore', () => ({
    usePrices: () => ({
        PricesState: PricesStateMock,
        createPrice: createPriceMock,
    }),
}));

const InputLabelStub = {
    name: 'InputLabel',
    props: {
        modelValue: String,
        labelText: String,
        required: Boolean,
        min: Number,
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
        createPriceMock.mockResolvedValue(true);
        PricesStateMock.prices = {
            2025: { price: 4.4, price_combined: 6.4, year: 2025 },
            2026: { price: 4.6, price_combined: 6.6, year: 2026 },
        };

        wrapper = mount(PricesCreationPanel, {
            global: {
                stubs: {
                    InputLabel: InputLabelStub,
                    SubmitButton: SubmitButtonStub,
                },
            },
        });
    });

    it('should show validation error due price is below minPrice', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        expect(inputs).toHaveLength(2);

        expect((inputs[0].element as HTMLInputElement).value).toBe('4.6');
        expect((inputs[1].element as HTMLInputElement).value).toBe('6.6');

        await inputs[0].setValue('4.0');

        expect(wrapper.text()).toContain('prices.errors.priceMinimum');
        expect(wrapper.text()).toContain('2027');
    });

    it('should submit form successfully', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        expect(inputs).toHaveLength(2);

        expect((inputs[0].element as HTMLInputElement).value).toBe('4.6');
        expect((inputs[1].element as HTMLInputElement).value).toBe('6.6');

        await wrapper.find('form').trigger('submit.prevent');

        expect(createPriceMock).toHaveBeenCalledTimes(1);
        expect(createPriceMock).toHaveBeenCalledWith({
            year: 2027,
            price: 4.6,
            price_combined: 6.6,
        });

        const isEventSubmitted = wrapper.emitted('closePanel');
        expect(isEventSubmitted).toBeTruthy();
        expect(wrapper.text()).toContain('2027');
    });

    it('should show general error due createPrice returns false', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        expect(inputs).toHaveLength(2);

        expect((inputs[0].element as HTMLInputElement).value).toBe('4.6');
        expect((inputs[1].element as HTMLInputElement).value).toBe('6.6');

        createPriceMock.mockResolvedValue(false);

        await wrapper.find('form').trigger('submit.prevent');

        expect(createPriceMock).toHaveBeenCalledTimes(1);
        expect(wrapper.text()).toContain('prices.errors.createFailed');
        expect(wrapper.emitted('closePanel')).toBeFalsy();
        expect(wrapper.text()).toContain('2027');
    });

    it('should show general error when createPrice throws error', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        expect(inputs).toHaveLength(2);

        expect((inputs[0].element as HTMLInputElement).value).toBe('4.6');
        expect((inputs[1].element as HTMLInputElement).value).toBe('6.6');

        createPriceMock.mockRejectedValue(new Error('test'));

        await wrapper.find('form').trigger('submit.prevent');

        expect(createPriceMock).toHaveBeenCalledTimes(1);
        expect(wrapper.text()).toContain('prices.errors.createFailed');
        expect(wrapper.emitted('closePanel')).toBeFalsy();
        expect(wrapper.text()).toContain('2027');
    });

    it('should show required errors if fields are empty', async () => {
        const inputs = wrapper.findAll('[data-testid="number-input"]');
        await inputs[0].setValue('');
        await inputs[1].setValue('');

        await wrapper.find('form').trigger('submit.prevent');

        expect(createPriceMock).not.toHaveBeenCalled();
        expect(wrapper.text()).toContain('prices.errors.priceRequired');
        expect(wrapper.text()).toContain('prices.errors.priceCombinedRequired');
        expect(wrapper.text()).toContain('2027');
    });
});
