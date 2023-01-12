<template>
  <span
    :class="checkboxCSS"
    @click="handle"
  >
    <CheckIcon
      v-if="isParticipating"
      class="relative top-[10%] left-[10%] h-[80%] w-[80%] text-white"
    />
  </span>
  <CombiModal
    :open="open"
    :weekID="weekID"
    :dayID="dayID"
    :meals="day.meals"
    @closeCombiModal="closeCombiModal"
  />
</template>

<script setup>
import { computed, ref } from 'vue'
import { useJoinMeal } from '@/api/postJoinMeal'
import { useLeaveMeal } from '@/api/postLeaveMeal'
import { useOfferMeal } from '@/api/postOfferMeal'
import { useCancelOffer } from '@/api/postCancelOffer'
import { dashboardStore } from '@/stores/dashboardStore'
import { CheckIcon } from '@heroicons/vue/solid'
import CombiModal from '@/components/dashboard/CombiModal.vue'

const props = defineProps(['weekID', 'dayID', 'mealID', 'variationID', 'meal', 'day'])

const day = props.day ? props.day : dashboardStore.getDay(props.weekID, props.dayID)
let meal
let mealId
if (props.variationID) {
  meal = props.meal ? props.meal : dashboardStore.getVariation(props.weekID, props.dayID, props.mealID, props.variationID)
  mealId = props.variationID
} else {
  meal = props.meal ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)
  mealId = props.mealID
}
console.log(props.meal)
const open = ref(false)
const isParticipating = computed(() => meal.isParticipating !== null)

const checkboxCSS = computed(() => {
  let cssResult = 'rounded-md h-[30px] w-[30px] xl:h-[20px] xl:w-[20px] '

  if(isParticipating.value === true) {
    switch (meal.mealState) {
      case 'disabled':
        cssResult += 'bg-[#B4C1CE] border-[0.5px] border-[#ABABAB]'
        return cssResult
      case 'open':
      case 'tradeable':
        cssResult += 'bg-primary-4 hover:bg-primary-3 cursor-pointer border-0'
        return cssResult
      case 'offering':
      case 'offerable':
        cssResult += 'bg-highlight cursor-pointer border-0'
        return cssResult
    }
  } else if (isParticipating.value === false) {
    switch (meal.mealState) {
      case 'disabled':
        cssResult += 'bg-[#EDEDED] border-[0.5px] border-[#ABABAB]'
        return cssResult
      case 'tradeable':
      case 'open':
        cssResult += 'cursor-pointer bg-[#FAFAFA] border-[0.5px] border-[#ABABAB]'
        return cssResult
    }
  }
  return cssResult
})

async function handle() {
  if(meal.mealState === 'open' || meal.mealState === 'tradeable') {
    if(isParticipating.value) {
      await leaveMeal()
    } else {
      let slugs = [meal.dishSlug]
      if(slugs[0] === 'combined-dish') {
        slugs = getDishSlugs()
        if(slugs === -1) return
      }
      await joinMeal(slugs)
    }
  } else {
    if(meal.mealState === 'offerable') {
      await sendOffer()
    } else if(meal.mealState === 'offering') {
      await cancelOffer()
    }
  }
}

async function handleGuest() {

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
    mealID: mealId,
    dishSlugs: dishSlugs,
    slotID: day.activeSlot
  }

  const { response, error } = await useJoinMeal(JSON.stringify(data))
  if (error.value === false) {
    day.activeSlot = response.value.slotId
    meal.isParticipating = response.value.participantId
    meal.mealState = response.value.mealState
  }
}

async function leaveMeal() {
  let data = {
    mealId: mealId
  }

  const { response, error } = await useLeaveMeal(JSON.stringify(data))
  if (error.value === false) {
    day.activeSlot = response.value.slotId
    meal.isParticipating = null
  }
}

async function sendOffer() {
  let data = {
    mealId: mealId
  }

  const { error } = await useOfferMeal(JSON.stringify(data))
  if (error.value === false) {
    meal.mealState = 'offering'
  }
}

async function cancelOffer() {
  let data = {
    mealId: mealId
  }

  const { error } = await useCancelOffer(JSON.stringify(data))
  if (error.value === false) {
    meal.mealState = 'offerable'
  }
}

async function closeCombiModal(slugs) {
  open.value = false
  if (slugs !== undefined) {
    await joinMeal(slugs)
  }
}

</script>