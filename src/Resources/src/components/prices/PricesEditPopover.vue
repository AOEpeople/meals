<template>
  <div class="w-[400px] rounded-lg bg-white p-6 shadow-lg">
    <h3 class="mb-4 text-lg font-semibold">Jahr {{ year }} {{ t('prices.edit') }}</h3>

    <form @submit.prevent="onSubmit">
      <InputLabel
        v-model="priceInput"
        :label-text="t('prices.popover.price')"
        type="number"
        step=".01"
        :error="errors.price"
        class="mb-3"
      />

      <InputLabel
        v-model="priceCombinedInput"
        :label-text="t('prices.popover.priceCombined')"
        type="number"
        step=".01"
        :error="errors.priceCombined"
        class="mb-4"
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InputLabel from '../misc/InputLabelNumber.vue';
import SubmitButton from '../misc/SubmitButton.vue';
import { usePrices } from '@/stores/pricesStore';

const { t } = useI18n();
const { getPriceByYear } = usePrices();
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

function parsePrice(value: string | number): number {
  const numValue = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value;
  return isNaN(numValue) ? 0 : numValue;
}

function validateForm(): boolean {
  let valid = true;
  errors.value = { price: '', priceCombined: '', general: '' };

  const price = parsePrice(priceInput.value);
  const priceCombined = parsePrice(priceCombinedInput.value);

  if (isNaN(price) || price <= 0) {
    errors.value.price = t('prices.errors.invalidPrice');
    valid = false;
  }

  if (isNaN(priceCombined) || priceCombined <= 0) {
    errors.value.priceCombined = t('prices.errors.invalidPrice');
    valid = false;
  }

  return valid;
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
  }
}
</script>
