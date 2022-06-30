<template>
  <span @click="handle"
       :class="[enabled
         ? disabled ? 'bg-[#80909F]' : 'bg-primary-4 hover:bg-primary-3 cursor-pointer'
         : disabled ? 'bg-[#FAFAFA] opacity-50' : 'cursor-pointer bg-[#FAFAFA] hover:bg-gray-100'
         , 'rounded-md h-[30px] w-[30px] xl:h-[20px] xl:w-[20px] border-[0.5px] border-gray-200'
       ]"
  >
    <svg class="relative top-[10px] xl:top-[6px] m-auto xl:w-[12px] xl:h-[7px] w-[18px] h-[10.5px]" v-if="enabled" viewBox="0 0 12 7" fill="none" fill-opacity="0%" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M1.54617 3.5L4.43387 6L10.2734 1" fill="#518AD5"/>
      <path d="M1.54617 3.5L4.43387 6L10.2734 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </span>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps(['mealData', 'disabled'])

const enabled = ref(props.mealData.isParticipating)

function handle() {
  if(!props.disabled) {
    enabled.value = !enabled.value
  }
}

</script>

<style scoped>

</style>