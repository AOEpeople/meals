<template>
  <div class="flex flex-row place-items-center gap-2">
    <RefreshIcon
      v-if="!errorStates.includes(true)"
      class="size-8 font-black text-green"
    />
    <ExclamationCircleIcon
      v-else
      class="size-8 animate-pulse font-black text-red"
    />
  </div>
</template>

<script setup lang="ts">
import { RefreshIcon, ExclamationCircleIcon } from '@heroicons/vue/outline';

defineProps<{
  errorStates: boolean[];
}>();
</script>
