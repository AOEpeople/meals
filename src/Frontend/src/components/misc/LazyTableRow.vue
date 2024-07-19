<template>
  <tr ref="elementRef">
    <slot v-if="shouldRender === true" />
    <div
      v-else
      :style="`min-height: ${fixedMinHeight !== 0 ? fixedMinHeight : minHeight}px`"
    />
  </tr>
</template>

<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useIntersectionObserver } from '@vueuse/core';

defineProps<{
  minHeight: number;
}>();

const shouldRender = ref(false);
const elementRef = ref<HTMLElement | null>(null);
const fixedMinHeight = ref(0);

const { stop } = useIntersectionObserver(
  elementRef,
  ([{ isIntersecting }]) => {
    if (isIntersecting === true) {
      shouldRender.value = true;
      stop();
    }
  },
  {
    rootMargin: '600px'
  }
);

onIdle(() => {
  shouldRender.value = true;
  stop();
});

function onIdle(cb = () => void 0) {
  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(cb);
  } else {
    setTimeout(() => {
      nextTick(cb);
    }, 300);
  }
}
</script>
