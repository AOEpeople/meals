<template>
  <Popover
    v-slot="{ open }"
    class="relative z-[100]"
  >
    <PopoverButton class="focus:outline-none">
      <slot
        name="button"
        :open="(open)"
      />
    </PopoverButton>

    <transition
      enter-active-class="transition ease-out duration-400"
      enter-from-class="translate-y-1 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-250"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-1 opacity-0"
    >
      <PopoverPanel
        v-slot="{ close }"
        class="absolute left-1/2 z-[101] mt-5 -translate-x-1/2 opacity-[99.9%] xl:-translate-x-3/4"
      >
        <div class="w-[95vw] overflow-hidden rounded-lg bg-gray-200 shadow-lg ring-1 ring-black/5 xl:w-auto">
          <slot
            name="panel"
            :close="close"
          />
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'

</script>