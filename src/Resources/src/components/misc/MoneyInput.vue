<template>
  <input
    v-model="value"
    type="number"
    required
    min="0.00"
    step=".01"
    class="h-[46px] rounded-full border-[2px] border-solid border-[#CAD6E1] bg-white text-center"
    @focus="(e) => selectAllAndPlaceCursor(e.target as HTMLInputElement)"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  modelValue: number;
}>();

const emit = defineEmits(['update:modelValue']);

const value = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', parseFloat(value.toFixed(2)));
  }
});

function selectAllAndPlaceCursor(element: HTMLInputElement) {
  element.select();
  element.focus();
  element.setSelectionRange(0, 0);
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
