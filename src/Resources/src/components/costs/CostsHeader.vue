<template>
  <div class="mb-8 grid w-full grid-cols-3 gap-3 sm:grid-rows-[minmax(0,1fr)_30px] min-[900px]:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] min-[900px]:gap-1">
    <h2 class="col-span-3 col-start-1 row-span-1 row-start-1 m-0 w-full self-center justify-self-start max-[380px]:text-[24px] min-[900px]:col-span-1">
      {{ t('costs.header') }}
    </h2>
    <CashRegisterLink
      class="col-span-3 row-start-2 justify-self-center sm:col-span-1 sm:col-start-1 sm:justify-self-start min-[900px]:row-start-2"
    />
    <InputLabel
      v-model="filter"
      :label-text="t('costs.search')"
    />
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import CashRegisterLink from './CashRegisterLink.vue';
import InputLabel from '../misc/InputLabel.vue';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps<{
  modelValue: string
}>();

const emit = defineEmits(['update:modelValue']);

const filter = computed({
  get() {
    return props.modelValue;
  },
  set(filter) {
    emit('update:modelValue', filter);
  }
})
</script>