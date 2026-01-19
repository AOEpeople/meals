<template>
  <input
    v-model="displayValue"
    type="text"
    inputmode="decimal"
    required
    class="h-[46px] rounded-full border-2 border-solid border-[#CAD6E1] bg-white text-center"
    @input="onInput"
    @focus="onFocus"
    @blur="onBlur"
  />
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  modelValue: number;
}>();

const { t } = useI18n();
const emit = defineEmits(['update:modelValue']);
const isFocused = ref(false);
const rawValue = ref(props.modelValue.toString());
const displayValue = computed(() => (isFocused.value ? rawValue.value : formatMoney(props.modelValue)));

function onInput(event: Event) {
  const value = (event.target as HTMLInputElement).value;
  rawValue.value = value;
  const parsed = parseFloat(value.replace(',', '.'));
  if (isNaN(parsed) || parsed < 0) {
    return;
  }
  emit('update:modelValue', round(parsed));
}

function onFocus() {
  isFocused.value = true;
  rawValue.value = props.modelValue.toString();
}

function onBlur() {
  isFocused.value = false;
  rawValue.value = formatMoney(props.modelValue);
}

function round(value: number) {
  return Math.round(value * 100) / 100;
}

function formatMoney(value: number) {
  return value.toLocaleString(t('languageCode'), {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield;
}
</style>
