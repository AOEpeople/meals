<template>
  <form
    class="w-[300px] p-4 sm:w-[400px]"
    @submit.prevent="onSubmit"
  >
    <h3 class="w-full text-center">
      {{ t('prices.popover.create') }}
    </h3>
    <div class="mb-4">
      <p class="text-sm text-gray-600">
        {{ t('prices.popover.year') }}: <strong>{{ nextYear }}</strong>
      </p>
    </div>
    <InputLabel
      id="create-price-per-dish-field"
      v-model="priceInput"
      :label-text="t('prices.popover.price')"
      :required="required"
      :min="minPrice"
      type="number"
      step=".01"
      :error="errors.price"
    />
    <InputLabel
      id="create-price-per-combined-dishes-field"
      v-model="priceCombinedInput"
      :label-text="t('prices.popover.priceCombined')"
      :required="required"
      :min="minPriceCombined"
      type="number"
      class="my-3"
      step=".01"
      :error="errors.priceCombined"
    />
    <p
      v-if="errors.general"
      class="text-red-600 mb-3 text-sm"
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
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import SubmitButton from '../misc/SubmitButton.vue';
import InputLabel from '../misc/InputLabelNumber.vue';
import { ref, computed, watch } from 'vue';
import { usePrices } from '@/stores/pricesStore';

const { t } = useI18n();
const { PricesState, createPrice } = usePrices();

const emit = defineEmits(['closePanel']);

const required = ref(false);
const isSubmitting = ref(false);

const errors = ref({
  price: '',
  priceCombined: '',
  general: ''
});

const nextYear = computed(() => {
  const years = Object.keys(PricesState.prices).map(Number);
  return years.length > 0 ? Math.max(...years) + 1 : new Date().getFullYear();
});

const lastYearPrices = computed(() => {
  const years = Object.keys(PricesState.prices)
    .map(Number)
    .sort((a, b) => b - a);
  if (years.length > 0) {
    return PricesState.prices[years[0]];
  }
  return { price: 0, price_combined: 0 };
});

const minPrice = computed(() => lastYearPrices.value.price);
const minPriceCombined = computed(() => lastYearPrices.value.price_combined);

const priceInput = ref<string>(minPrice.value.toString());
const priceCombinedInput = ref<string>(minPriceCombined.value.toString());

watch(priceInput, (newValue) => {
  const numValue = parseFloat(newValue);
  if (newValue && (isNaN(numValue) || numValue < minPrice.value)) {
    errors.value.price = t('prices.errors.priceMinimum', { min: minPrice.value });
  } else {
    errors.value.price = '';
  }
});

watch(priceCombinedInput, (newValue) => {
  const numValue = parseFloat(newValue);
  if (newValue && (isNaN(numValue) || numValue < minPriceCombined.value)) {
    errors.value.priceCombined = t('prices.errors.priceCombinedMinimum', { min: minPriceCombined.value });
  } else {
    errors.value.priceCombined = '';
  }
});

function validateForm(): boolean {
  errors.value = { price: '', priceCombined: '', general: '' };
  let isValid = true;
  const priceValue = parseFloat(priceInput.value);
  const priceCombinedValue = parseFloat(priceCombinedInput.value);
  if (!priceInput.value || isNaN(priceValue)) {
    errors.value.price = t('prices.errors.priceRequired');
    isValid = false;
  } else if (priceValue < minPrice.value) {
    errors.value.price = t('prices.errors.priceMinimum', { min: minPrice.value });
    isValid = false;
  }

  if (!priceCombinedInput.value || isNaN(priceCombinedValue)) {
    errors.value.priceCombined = t('prices.errors.priceCombinedRequired');
    isValid = false;
  } else if (priceCombinedValue < minPriceCombined.value) {
    errors.value.priceCombined = t('prices.errors.priceCombinedMinimum', { min: minPriceCombined.value });
    isValid = false;
  }

  return isValid;
}

async function onSubmit() {
  required.value = true;
  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;
  try {
    const success = await createPrice({
      year: nextYear.value,
      price: parseFloat(priceInput.value),
      price_combined: parseFloat(priceCombinedInput.value)
    });

    if (success) {
      emit('closePanel');
    } else {
      errors.value.general = t('prices.errors.createFailed');
    }
  } catch (error) {
    errors.value.general = t('prices.errors.createFailed');
  } finally {
    isSubmitting.value = false;
  }
}
</script>
