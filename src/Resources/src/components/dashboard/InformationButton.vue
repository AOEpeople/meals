<template>
  <InformationCircleIcon
    class="relative h-[16px] w-[16px] text-white"
  />
</template>

<script setup lang="ts">
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { InformationCircleIcon } from '@heroicons/vue/outline';

defineProps<{
  dayID: number | string,
  index: number
}>();

</script>