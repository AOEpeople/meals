<template>
  <Popover
    v-slot="{ open }"
  >
    <PopoverButton class="h-full focus:outline-none">
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
        class="absolute z-[101] mx-auto mt-5 opacity-[99.9%]"
        :style="{ transform: `translateX(${translateXComputed})` }"
      >
        <div class="overflow-hidden rounded-lg bg-[rgb(244,247,249)] shadow-lg ring-1 ring-black/5">
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
import { computed, onMounted, onUnmounted } from 'vue';
import { useComponentHeights } from "@/services/useComponentHeights";

const { windowWidth, addWindowHeightListener, removeWindowHeightListener } = useComponentHeights();

const props = withDefaults(defineProps<{
  translateXMax?: string,
  translateXMin?: string,
  breakpointWidth?: number
}>(), {
  translateXMax: 'default',
  translateXMin: 'default',
  breakpointWidth: 1200
});

onMounted(() => {
  addWindowHeightListener();
});

onUnmounted(() => {
  removeWindowHeightListener();
})

const translateXComputed = computed(() => {
  if(windowWidth.value >= props.breakpointWidth) {
    return props.translateXMax === 'default' ? '-80%' : props.translateXMax
  } else {
    return props.translateXMin === 'default' ? '-80%' : props.translateXMin
  }
});
</script>