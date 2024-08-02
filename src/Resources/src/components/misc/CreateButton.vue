<template>
  <div
    :class="[open || pressed ? 'translate-y-0.5' : 'btn-highlight-shadow']"
    class="mx-2 mb-6 mt-4 flex h-9 items-center justify-center rounded-btn bg-highlight px-[34px] text-center text-btn font-bold leading-[10px] text-white drop-shadow-btn transition-all duration-300 ease-out hover:bg-[#f7a043] focus:outline-none"
    @mousedown="handleMouseDown"
    @mouseup="handleMouseUp"
  >
    {{ btnText }}
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = withDefaults(
  defineProps<{
    btnText: string;
    open?: boolean;
    managed?: boolean;
  }>(),
  {
    open: false,
    managed: false
  }
);

const pressed = ref(false);

function handleMouseDown() {
  if (props.managed === true) {
    pressed.value = true;
  }
}

function handleMouseUp() {
  if (props.managed === true) {
    pressed.value = false;
  }
}
</script>
