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
  <CombiModal :open="open" :meals="meals_no_combined" @closeCombiModal="closeCombiModal"/>
</template>

<script setup>
import { ref } from 'vue'
import { useJoinMeal } from '@/hooks/postJoinMeal'
import { useLeaveMeal } from '@/hooks/postLeaveMeal'
import { dashboardStore } from '@/store/dashboardStore'
import CombiModal from "@/components/dashboard/CombiModal.vue";

const props = defineProps(['mealData', 'disabled', 'dayId'])

let meals_no_combined = []
if(!props.disabled) {
  meals_no_combined = dashboardStore.getMealsByDayId(props.dayId, true)
}

const open = ref(false)
const enabled = ref(props.mealData.isParticipating)

async function handle() {
  if(!props.disabled) {
    if(enabled.value) {
      await leaveMeal()
    } else {
      let slugs = [props.mealData.dishSlug]
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
  for (let meal of meals_no_combined) {
    if(meal.variations) {
      open.value = true
      return -1
    }
    slugs.push(meal.dishSlug)
  }

  return slugs
}

async function joinMeal(dishSlugs) {
  let data = {
    mealID: props.mealData.id,
    dishSlugs: dishSlugs,
    slotID: dashboardStore.getDayById(props.dayId)?.activeSlot
  }

  const error = await useJoinMeal(JSON.stringify(data))
  if(error.value === false) {
    enabled.value = true
  }
}

async function leaveMeal() {
  let data = {
    mealID: props.mealData.id
  }

  const error = await useLeaveMeal(JSON.stringify(data))
  if(error.value === false) {
    enabled.value = false
  }
}

async function closeCombiModal(slugs) {
  open.value = false
  if(slugs !== undefined) {
    await joinMeal(slugs)
  }
}

</script>