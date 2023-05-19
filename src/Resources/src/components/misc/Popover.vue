<template>
  <Popover
    v-slot="{ open }"
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
        class="absolute z-[101] mx-auto mt-5 opacity-[99.9%] xl:-translate-x-[80%]"
        :style="{ transform: `translateX(${translateX})` }"
      >
        <div class="overflow-hidden rounded-lg bg-gray-200 shadow-lg ring-1 ring-black/5">
          <slot
            name="panel"
            :close="close"
          />
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script setup lang="ts">
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'

withDefaults(defineProps<{
  translateX: string
}>(), {
  translateX: '0%'
});
</script>