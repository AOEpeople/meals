<template>
  <span @click="handle" :class="checkboxCSS">
    <CheckIcon v-if="enabled && (mealState === 'open' || mealState === 'disabled')" class="text-white w-[80%] h-[80%] relative top-[10%] left-[10%]" />
    <LockClosedIcon v-if="enabled && mealState === 'offerable'" class="text-white w-[80%] h-[80%] relative top-[10%] left-[10%]" />
    <LockOpenIcon v-if="enabled && mealState === 'offering'" class="text-white w-[80%] h-[80%] relative top-[10%] left-[10%]"/>
  </span>
  <CombiModal
      :open="open"
      :weekID="weekID"
      :dayID="dayID"
      @closeCombiModal="closeCombiModal"/>
</template>

<script setup>
import {computed, ref} from 'vue'
import { useJoinMeal } from '@/hooks/postJoinMeal'
import { useLeaveMeal } from '@/hooks/postLeaveMeal'
import { useOfferMeal } from '@/hooks/postOfferMeal'
import { useCancelOffer } from '@/hooks/postCancelOffer'
import { dashboardStore } from '@/store/dashboardStore'
import { LockClosedIcon, LockOpenIcon, CheckIcon } from '@heroicons/vue/solid'
import CombiModal from '@/components/dashboard/CombiModal.vue'

const props = defineProps([
  'weekID',
  'dayID',
  'mealID',
  'variationID',
  'mealState',
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

const checkboxCSS = computed(() => {
  let cssResult = 'rounded-md h-[30px] w-[30px] xl:h-[20px] xl:w-[20px] border-[0.5px] border-gray-200 '

  if(enabled.value) {
    switch (props.mealState) {
      case 'disabled':
        cssResult += 'bg-[#80909F]'
        return cssResult
      case 'tradeable':
      case 'open':
        cssResult += 'bg-primary-4 hover:bg-primary-3 cursor-pointer'
        return cssResult
      case 'offering':
      case 'offerable':
        cssResult += 'bg-highlight cursor-pointer'
        return cssResult
    }
  } else {
    switch (props.mealState) {
      case 'disabled':
        cssResult += 'bg-[#FAFAFA] opacity-50'
        return cssResult
      case 'tradeable':
      case 'open':
        cssResult += 'cursor-pointer bg-[#FAFAFA] hover:bg-gray-100'
        return cssResult
    }
  }
})

async function handle() {
  if(props.mealState === 'open' || props.mealState === 'tradeable') {
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
  if(props.mealState === 'offerable') {
    await sendOffer()
    return
  }
  if(props.mealState === 'offering') {
    await cancelOffer()
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

async function sendOffer() {
  let data = {
    'mealId': props.mealID
  }

  const { error } = await useOfferMeal(JSON.stringify(data))
  if (error.value === false) {
    meal.offerStatus = true
  }
}

async function cancelOffer() {
  let data = {
    'mealId': props.mealID
  }

  const { error } = await useCancelOffer(JSON.stringify(data))
  if (error.value === false) {
    meal.offerStatus = false
  }
}

async function closeCombiModal(slugs) {
  open.value = false
  if (slugs !== undefined) {
    await joinMeal(slugs)
  }
}

</script>