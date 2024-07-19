<template>
  <div class="h-5 min-[380px]:h-auto">
    <span class="whitespace-nowrap text-[11px] font-light text-primary min-[370px]:text-note">
      {{ localePrice }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const props = defineProps<{
  price: number;
}>();

const localePrice = computed(() =>
  locale.value.substring(0, 2) === 'en'
    ? '€ ' + props.price.toFixed(2)
    : props.price.toFixed(2).replace(/\./g, ',') + ' €'
);
</script>
