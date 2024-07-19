<template>
  <div :class="[result === 'resolve_success' ? 'bg-lime-800' : 'bg-rose', 'm-16 rounded']">
    <h2 class="p-8 text-center text-white">
      {{ t('guest.' + result) }}
    </h2>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
  result: string;
}>();
</script>
