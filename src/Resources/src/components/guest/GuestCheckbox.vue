<template>
  <span
    :class="[enabled ? 'bg-primary-3' : '', 'rounded-md h-[30px] w-[30px] cursor-pointer xl:h-[20px] xl:w-[20px] border-[0.5px] border-gray-200']"
    @click="handle"
  >
    <CheckIcon
      v-if="enabled"
      class="relative top-[10%] left-[10%] h-[80%] w-[80%] text-white"
    />
  </span>
  <CombiModal
    v-if="isCombiBox"
    :open="open"
    :meals="meals"
    @closeCombiModal="handleCombiModal"
  />
</template>

<script setup>
import { CheckIcon } from '@heroicons/vue/solid'
import { ref } from 'vue'
import useEventsBus from 'tools/eventBus'
import CombiModal from '@/components/dashboard/CombiModal.vue'

const props = defineProps(['meals', 'mealId'])
const enabled = ref(false)
const open = ref(false)
const { emit } = useEventsBus()

const isCombiBox = props.meals[props.mealId].dishSlug === 'combined-dish'
let hasVariations = false

Object.values(props.meals)
    .forEach((meal) => meal.variations ? hasVariations = true : '')

function handle() {
  if (isCombiBox) {
    if (hasVariations) {
      open.value = true
    } else {
      let combiDishes = Object.values(props.meals)
          .filter((meal) => meal.dishSlug !== 'combined-dish')
          .map((meal) => meal.dishSlug)

      emit('guestChosenCombi', combiDishes)
      emit('guestChosenMeals', props.mealId)
      enabled.value = !enabled.value
    }
  } else {
    emit('guestChosenMeals', props.mealId)
    enabled.value = !enabled.value
  }
}

function handleCombiModal(dishes) {
  if (dishes !== undefined) {
    emit('guestChosenCombi', dishes)
    emit('guestChosenMeals', props.mealId)
    enabled.value = !enabled.value
  }
  open.value = false
}

</script>

<style scoped>

</style>