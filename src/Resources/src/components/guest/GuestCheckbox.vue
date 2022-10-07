<template>
  <span @click="handle" :class="[enabled ? 'bg-primary' : '', 'rounded-md h-[30px] w-[30px] cursor-pointer xl:h-[20px] xl:w-[20px] border-[0.5px] border-gray-200']">
    <CheckIcon v-if="enabled" class="text-white w-[80%] h-[80%] relative top-[10%] left-[10%]" />
  </span>
</template>

<script setup>
import { CheckIcon } from '@heroicons/vue/solid'
import { ref } from 'vue'

const props = defineProps(['meal'])
const emit = defineEmits(['processMeal'])
const enabled = ref(false)

function handle() {
  emit('processMeal', props.meal.dishSlug)
  enabled.value = !enabled.value
}
</script>

<style scoped>

</style>