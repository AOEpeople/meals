<template>
  <input
    v-model="value"
    type="text"
    pattern="\d*([.,]?\d{0,2})"
    required
    class="h-[46px] rounded-full border-[2px] border-solid border-[#CAD6E1] bg-white text-center"
  >
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
  modelValue: number
}>();

const { locale } = useI18n();

const emit = defineEmits(['update:modelValue']);

const value = computed({
  get() {
    console.log(`Getting Money: ${props.modelValue}`);
    return locale.value === 'en' ? props.modelValue.toFixed(2) : props.modelValue.toFixed(2).replace(/\./g, ',');
  },
  set(value) {
    console.log(`Emitting Money: ${value.replace(/,/, '.')}`);
    emit('update:modelValue', parseFloat(value.replace(/,/, '.')));
  }
})
</script>