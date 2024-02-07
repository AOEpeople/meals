<template>
  <Popover class="relative">
    <PopoverButton>
      <Icons
        icon="guest"
        box="0 0 13 13"
        class="size-[13px]"
        :class="iconWhite ? 'fill-white' : 'fill-[#1c5298]'"
      />
    </PopoverButton>

    <PopoverPanel :class="[index === 4 ? '-top-12' : 'top-12', 'absolute -left-6 z-[2]']">
      <GuestLink
        :dayID="String(dayID)"
        :invitation="invitation"
      />
    </PopoverPanel>
  </Popover>
</template>

<script setup lang="ts">
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import Icons from '@/components/misc/Icons.vue';
import GuestLink from '@/components/dashboard/GuestLink.vue';
import { Invitation } from '@/enums/Invitation';

defineProps<{
  dayID: number | string;
  index: number;
  invitation: Invitation;
  iconWhite: boolean;
}>();
</script>
