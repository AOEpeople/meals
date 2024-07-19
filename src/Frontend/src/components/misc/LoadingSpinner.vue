<template>
  <div
    v-if="loaded === false"
    class="absolute top-0 z-[1] m-auto grid size-full max-h-[70vh] max-w-screen-aoe place-content-center overflow-hidden pt-[10vh]"
  >
    <RefreshIcon
      class="aspect-[1/1] h-[250px] animate-spin-loading text-primary drop-shadow-[0_0_0.35rem_rgba(0,0,0,0.75)]"
    />
  </div>
</template>

<script setup lang="ts">
import { RefreshIcon } from '@heroicons/vue/outline';

defineProps<{
  loaded: boolean;
}>();
</script>
