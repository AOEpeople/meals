<template>
  <li
    ref="elementRef"
    :style="`min-height: ${fixedMinHeight !== 0 ? fixedMinHeight : minHeight}px`"
  >
    <slot v-if="shouldRender === true" />
  </li>
</template>

<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { useIntersectionObserver } from '@vueuse/core';

const props = withDefaults(
  defineProps<{
    renderOnIdle?: boolean;
    unrender?: boolean;
    minHeight: number;
    unrenderDelay?: number;
  }>(),
  {
    renderOnIdle: false,
    unrender: false,
    unrenderDelay: 10000
  }
);

const shouldRender = ref(false);
const elementRef = ref<HTMLElement | null>(null);
const fixedMinHeight = ref(0);

let unrenderTimer: NodeJS.Timeout;
let renderTimer: NodeJS.Timeout;

const { stop } = useIntersectionObserver(
  elementRef,
  ([{ isIntersecting }]) => {
    if (isIntersecting === true) {
      clearTimeout(unrenderTimer);
      if (props.unrender) {
        renderTimer = setTimeout(() => (shouldRender.value = true), props.unrender === true ? 200 : 0);
      } else {
        shouldRender.value = true;
      }

      if (props.unrender === false) {
        stop();
      }
    } else if (props.unrender === true) {
      clearTimeout(renderTimer);
      unrenderTimer = setTimeout(() => {
        fixedMinHeight.value = elementRef.value !== null ? elementRef.value.clientHeight : props.minHeight;
        shouldRender.value = false;
      }, props.unrenderDelay);
    }
  },
  {
    rootMargin: '600px'
  }
);

if (props.renderOnIdle) {
  onIdle(() => {
    shouldRender.value = true;
    if (props.unrender === false) {
      stop();
    }
  });
}

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
