<template>
  <Popover v-slot="{ open }">
    <PopoverButton class="h-full align-middle focus:outline-none">
      <slot
        name="button"
        :open="open"
      />
    </PopoverButton>

    <transition
      enterActiveClass="transition ease-out duration-400"
      enterFromClass="translate-y-1 opacity-0"
      enterToClass="translate-y-0 opacity-100"
      leaveActiveClass="transition ease-in duration-250"
      leaveFromClass="translate-y-0 opacity-100"
      leaveToClass="translate-y-1 opacity-0"
    >
      <PopoverPanel
        v-slot="{ close }"
        class="absolute z-[101] mx-auto mt-5 opacity-[99.9%]"
        :style="{ transform: `translateX(${translateXComputed})` }"
        :class="popupStyles"
      >
        <div
          class="rounded-lg bg-[rgb(244,247,249)] shadow-lg ring-1 ring-black/5"
          :class="{ overflowHidden: 'overflow-hidden' }"
        >
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
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue';
import { computed, onMounted, onUnmounted } from 'vue';
import { useComponentHeights } from '@/services/useComponentHeights';

const { windowWidth, addWindowHeightListener, removeWindowHeightListener } = useComponentHeights();

const props = withDefaults(
  defineProps<{
    translateXMax?: string;
    translateXMin?: string;
    breakpointWidth?: number;
    overflowHidden?: boolean;
    popupStyles?: string;
  }>(),
  {
    translateXMax: 'default',
    translateXMin: 'default',
    breakpointWidth: 1200,
    overflowHidden: true,
    popupStyles: ''
  }
);

onMounted(() => {
  addWindowHeightListener();
});

onUnmounted(() => {
  removeWindowHeightListener();
});

const translateXComputed = computed(() => {
  if (windowWidth.value >= props.breakpointWidth) {
    return props.translateXMax === 'default' ? '-80%' : props.translateXMax;
  } else {
    return props.translateXMin === 'default' ? '-80%' : props.translateXMin;
  }
});
</script>
