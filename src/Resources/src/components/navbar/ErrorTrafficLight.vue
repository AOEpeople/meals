<template>
  <div class="flex flex-row place-items-center gap-2">
    <RefreshIcon
      v-if="!errorStates.includes(true)"
      class="h-8 w-8 font-black text-green"
    />
    <ExclamationCircleIcon
      v-else
      class="h-8 w-8 animate-pulse font-black text-red"
    />
  </div>
</template>

<script setup lang="ts">
import { RefreshIcon, ExclamationCircleIcon } from '@heroicons/vue/outline';

defineProps<{
  errorStates: boolean[]
}>();
</script>