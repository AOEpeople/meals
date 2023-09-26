<template>
  <Popover class="relative">
    <PopoverButton>
      <InformationCircleIcon
        class="relative h-[16px] w-[16px] text-white"
      />
    </PopoverButton>

    <PopoverPanel :class="[index === 4 ? '-top-12' : 'top-12' , 'absolute -left-6 z-[2]']">
      <GuestLink :dayID="String(dayID)" />
    </PopoverPanel>
  </Popover>
</template>

<script setup lang="ts">
import GuestLink from '@/components/dashboard/GuestLink.vue';
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { InformationCircleIcon } from '@heroicons/vue/outline';

defineProps<{
  dayID: number | string,
  index: number
}>();

</script>