<template>
  <InputLabel
    v-model="filterInput"
    :label-text="t('dish.search')"
    :label-visible="false"
    :x-button-active="true"
  />
</template>

<script setup lang="ts">
import InputLabel from '@/components/misc/InputLabel.vue';
import { useDishes } from '@/stores/dishesStore';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { setFilter } = useDishes();
const filterInput = ref('');

watch(
  () => filterInput.value,
  () => setFilter(filterInput.value)
);
</script>
