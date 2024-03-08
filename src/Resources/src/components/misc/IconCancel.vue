<template>
  <XIcon
    box="0 0 20 20"
    class="size-[20px] flex-none fill-primary"
    @mousedown="buttonPressed = true"
    @mouseup="buttonPressed = false"
  />
</template>

<script setup lang="ts">
import { XIcon } from '@heroicons/vue/solid';
import { ref } from 'vue';

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
