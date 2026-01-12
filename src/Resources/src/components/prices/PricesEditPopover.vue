<template>
  <div class="w-[300px] rounded-lg bg-white p-6 shadow-lg md:w-[400px]">
    <h3 class="mb-4 text-lg font-semibold">Jahr {{ year }} {{ t('prices.edit') }}</h3>

    <form @submit.prevent="onSubmit">
      <InputLabel
        id="edit-price-per-dish-field"
        v-model="priceInput"
        :label-text="t('prices.popover.price')"
        :min="minPrice"
        :max="maxPrice"
        type="number"
        step=".01"
        :error="errors.price"
        class="mb-3"
      />

      <InputLabel
        id="edit-price-per-combined-dishes-field"
        v-model="priceCombinedInput"
        :label-text="t('prices.popover.priceCombined')"
        :min="minPriceCombined"
        :max="maxPriceCombined"
        type="number"
        step=".01"
        :error="errors.priceCombined"
        class="mb-4"
      />

      <p
        v-if="errors.general"
        class="mb-3 text-sm font-medium text-[#DC2626]"
      >
        {{ errors.general }}
      </p>

      <div
        v-if="isSubmitting"
        class="text-center text-gray-500"
      >
        {{ t('prices.submitting') }}
      </div>
      <SubmitButton
        v-else
        :disabled="false"
      />
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import InputLabel from '../misc/InputLabelNumber.vue';
import SubmitButton from '../misc/SubmitButton.vue';
import { usePrices } from '@/stores/pricesStore';

const { t } = useI18n();
const { PricesState, getPriceByYear } = usePrices();
const isSubmitting = ref(false);

const props = defineProps<{
  year: number;
}>();

const emit = defineEmits<{
  update: [data: { year: number; price: number; price_combined: number }];
  close: [];
}>();

const priceInput = ref('');
const priceCombinedInput = ref('');

const errors = ref({
  price: '',
  priceCombined: '',
  general: ''
});

onMounted(() => {
  const currentPrice = getPriceByYear(props.year);
  if (currentPrice) {
    priceInput.value = String(currentPrice.price.toFixed(2));
    priceCombinedInput.value = String(currentPrice.price_combined.toFixed(2));
  }
});

watch(
  () => props.year,
  () => {
    isSubmitting.value = false;
    errors.value = { price: '', priceCombined: '', general: '' };

    const currentPrice = getPriceByYear(props.year);
    if (currentPrice) {
      priceInput.value = String(currentPrice.price.toFixed(2));
      priceCombinedInput.value = String(currentPrice.price_combined.toFixed(2));
    }
  }
);

const lastYearPrices = computed(() => {
  const years = Object.keys(PricesState.prices)
    .map(Number)
    .filter((year) => year < props.year)
    .sort((a, b) => b - a);
  if (years.length > 0) {
    return PricesState.prices[years[0]];
  }
  return { price: 0, price_combined: 0 };
});
const minPrice = computed(() => lastYearPrices.value.price);
const minPriceCombined = computed(() => lastYearPrices.value.price_combined);

const nextYearPrices = computed(() => {
  const years = Object.keys(PricesState.prices)
    .map(Number)
    .filter((year) => year > props.year)
    .sort((a, b) => a - b);
  if (years.length > 0) {
    return PricesState.prices[years[0]];
  }
  return { price: undefined, price_combined: undefined };
});
const maxPrice = computed(() => nextYearPrices.value.price);
const maxPriceCombined = computed(() => nextYearPrices.value.price_combined);

watch(priceInput, (newValue) => {
  const numValue = parseFloat(newValue);
  errors.value.price = '';
  if (newValue && (isNaN(numValue) || numValue < minPrice.value)) {
    const minPriceAsCurrency = new Intl.NumberFormat('de-DE', {
      style: 'currency',
      currency: 'EUR'
    }).format(minPrice.value);
    errors.value.price = t('prices.errors.priceMinimum', { min: minPriceAsCurrency });
  }
  if (maxPrice.value !== undefined && newValue && (isNaN(numValue) || numValue > maxPrice.value)) {
    const maxPriceAsCurrency = new Intl.NumberFormat('de-DE', {
      style: 'currency',
      currency: 'EUR'
    }).format(maxPrice.value);
    errors.value.price = t('prices.errors.priceMaximum', { max: maxPriceAsCurrency });
  }
});

watch(priceCombinedInput, (newValue) => {
  const numValue = parseFloat(newValue);
  errors.value.priceCombined = '';
  if (newValue && (isNaN(numValue) || numValue < minPriceCombined.value)) {
    const minPriceCombinedAsCurrency = new Intl.NumberFormat('de-DE', {
      style: 'currency',
      currency: 'EUR'
    }).format(minPriceCombined.value);
    errors.value.priceCombined = t('prices.errors.priceCombinedMinimum', { min: minPriceCombinedAsCurrency });
  }
  if (maxPriceCombined.value !== undefined && newValue && (isNaN(numValue) || numValue > maxPriceCombined.value)) {
    const maxPriceCombinedAsCurrency = new Intl.NumberFormat('de-DE', {
      style: 'currency',
      currency: 'EUR'
    }).format(maxPriceCombined.value);
    errors.value.priceCombined = t('prices.errors.priceCombinedMaximum', { max: maxPriceCombinedAsCurrency });
  }
});

function parsePrice(value: string | number): number {
  const numValue = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value;
  return isNaN(numValue) ? 0 : numValue;
}

function validateForm(): boolean {
  let isValid = true;
  errors.value = { price: '', priceCombined: '', general: '' };
  const price = parsePrice(priceInput.value);
  const priceCombined = parsePrice(priceCombinedInput.value);
  if (!price || isNaN(price)) {
    errors.value.price = t('prices.errors.priceRequired');
    isValid = false;
  }
  if (!priceCombined || isNaN(priceCombined)) {
    errors.value.priceCombined = t('prices.errors.priceCombinedRequired');
    isValid = false;
  }

  return isValid;
}

async function onSubmit() {
  if (!validateForm() || isSubmitting.value) return;

  isSubmitting.value = true;
  try {
    emit('update', {
      year: props.year,
      price: parsePrice(priceInput.value),
      price_combined: parsePrice(priceCombinedInput.value)
    });
  } catch (error) {
    errors.value.general = t('prices.errors.updateFailed');
    isSubmitting.value = false;
  } finally {
    isSubmitting.value = false;
  }
}
</script>
