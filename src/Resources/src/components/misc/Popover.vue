<template>
  <Popover class="relative" v-slot="{ open }">

    <PopoverButton>
      <slot name="button" :open="open"></slot>
    </PopoverButton>

    <transition
        enter-active-class="transition ease-out duration-400"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-250"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
    >
      <PopoverPanel class="absolute left-1/2 mt-5 transform -translate-x-1/2 xl:-translate-x-3/4">
        <div class="overflow-hidden bg-gray-200 rounded-lg ring-1 ring-black ring-opacity-5 shadow-lg w-[95vw] xl:w-auto">
          <slot name="panel"></slot>
        </div>
      </PopoverPanel>
    </transition>

  </Popover>
</template>

<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'

</script>