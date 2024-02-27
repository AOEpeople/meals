<template>
  <div
    class="grid aspect-square h-[30px] rounded-md xl:h-[20px] print:hidden"
    :class="isActive ? activeClass : inactiveClass"
  >
    <CheckIcon
      v-if="isChecked"
      class="place-self-center text-white"
    />
  </div>
</template>

<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/solid';
import { computed } from 'vue';

const props = defineProps<{
  isChecked: boolean;
  isActive: boolean;
}>();

const activeClass = computed(() => {
  if (props.isChecked === true) {
    return 'bg-primary-4 hover:bg-primary-3 cursor-pointer';
  }
  return 'bg-[#FAFAFA] border-[#ABABAB] border-[0.5px] cursor-pointer';
});

const inactiveClass = computed(() => {
  if (props.isChecked === true) {
    return 'border-[#ABABAB] bg-[#B4C1CE] border-[0.5px]';
  }
  return 'bg-[#EDEDED] border-[#ABABAB] border-[0.5px]';
});
</script>
