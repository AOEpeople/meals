<template>
  <Popover class="relative" v-slot="{ open }">

    <PopoverButton>
      <slot name="button" :open="open"></slot>
    </PopoverButton>

    <transition
        enter-active-class="transition ease-out duration-400"
        enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-250"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-1 opacity-0"
    >
      <PopoverPanel v-slot="{ close }" class="absolute left-1/2 mt-5 -translate-x-1/2 transform xl:-translate-x-3/4">
        <div class="overflow-hidden bg-gray-200 rounded-lg ring-1 ring-black ring-opacity-5 shadow-lg w-[95vw] xl:w-auto">
          <slot name="panel" :close="close"></slot>
        </div>
      </PopoverPanel>
    </transition>

  </Popover>
</template>

<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'

</script>