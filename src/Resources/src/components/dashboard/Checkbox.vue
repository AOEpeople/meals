<template>
  <span @click="handle"
       :class="[meal.isParticipating
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
  <CombiModal
      :open="open"
      :weekID="weekID"
      :dayID="dayID"
      @closeCombiModal="closeCombiModal"/>
</template>

<script setup>
import { ref } from 'vue'
import { useJoinMeal } from '@/hooks/postJoinMeal'
import { useLeaveMeal } from '@/hooks/postLeaveMeal'
import { dashboardStore } from '@/store/dashboardStore'
import CombiModal from "@/components/dashboard/CombiModal.vue";

const props = defineProps([
  'weekID',
  'dayID',
  'mealID',
  'variationID',
  'disabled',
])

let day = dashboardStore.getDay(props.weekID, props.dayID)

let meal
if(props.variationID) {
  meal = dashboardStore.getVariation(props.weekID, props.dayID, props.mealID, props.variationID)
} else {
  meal = dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)
}

const open = ref(false)
const enabled = ref(meal.isParticipating)

async function handle() {
  if(!props.disabled) {
    if(enabled.value) {
      await leaveMeal()
    } else {
      let slugs = [meal.dishSlug]
      if(slugs[0] === 'combined-dish') {
        slugs = getDishSlugs()
        if(slugs === -1) return
      }
      await joinMeal(slugs)
    }
  }
}

function getDishSlugs() {
  let slugs = []
  for (let mealID in day.meals) {
    if (day.meals[mealID].variations) {
      open.value = true
      return -1
    } else {
      if (day.meals[mealID].dishSlug !== "combined-dish") {
        slugs.push(day.meals[mealID].dishSlug)
      }
    }
  }

  return slugs
}

async function joinMeal(dishSlugs) {
  let data = {
    mealID: props.variationID ? props.variationID : props.mealID,
    dishSlugs: dishSlugs,
    slotID: day.activeSlot
  }

  const { response, error } = await useJoinMeal(JSON.stringify(data))
  if (error.value === false) {
    enabled.value = true
    day.activeSlot = response.value.slotID
    meal.isParticipating = true
  }
}

async function leaveMeal() {
  let data = {
    mealID: props.variationID ? props.variationID : props.mealID
  }

  const { response, error } = await useLeaveMeal(JSON.stringify(data))
  if (error.value === false) {
    enabled.value = false
    day.activeSlot = response.value.slotID
    meal.isParticipating = false
  }
}

async function closeCombiModal(slugs) {
  open.value = false
  if (slugs !== undefined) {
    await joinMeal(slugs)
  }
}

</script>