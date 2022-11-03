<template>
  <Popover class="relative">
    <PopoverButton>
      <Icons icon="guest" box="0 0 13 13" class="w-[13px] h-[13px] fill-white"/>
    </PopoverButton>

    <PopoverPanel :class="[index === 4 ? '-top-12' : 'top-12' , 'absolute -left-6 z-[2]']">
      <GuestLink :dayID="dayID"/>
    </PopoverPanel>
  </Popover>
</template>

<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import Icons from '@/components/misc/Icons.vue'
import GuestLink from "@/components/dashboard/GuestLink.vue";

const props = defineProps(['dayID', 'index'])

</script>

<style scoped>

</style>