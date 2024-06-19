<template>
  <div
    :class="[buttonPressed ? 'translate-y-0.5' : 'light-shadow']"
    class="mx-auto mb-6 mt-4 flex h-9 w-[132px] items-center justify-center rounded-btn bg-white px-[34px] text-center text-btn font-bold leading-[10px] text-primary-2 drop-shadow-btn transition-all duration-300 ease-out hover:text-cyan focus:outline-none"
    @mousedown="buttonPressed = true"
    @mouseup="buttonPressed = false"
  >
    {{ btnText }}
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
  btnText: string;
}>();

const buttonPressed = ref(false);
</script>

<style scoped>
.light-shadow {
  box-shadow:
    0px 0px 0px 0px rgba(0, 0, 0, 0),
    0px 0px 0px 0px rgba(0, 0, 0, 0),
    0px 3px 0 0 rgba(192, 210, 226, 1);
}
</style>
