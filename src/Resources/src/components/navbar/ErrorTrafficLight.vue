<template>
  <div class="flex flex-row place-items-center gap-2">
    <p class="my-auto text-center font-light">
      Status
    </p>
    <div
      class="h-4 w-4 rounded-full border-0 border-none"
      :class="trafficLightColor"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  errorStates: boolean[]
}>();

const trafficLightColor = computed(() => {
  return props.errorStates.includes(true) ? 'bg-red' : 'bg-green';
})
</script>