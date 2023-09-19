<template>
  <span
    :class="checkboxCSS"
    @click="handle"
  >
    <CheckIcon
      v-if="isParticipating"
      class="relative left-[10%] top-[10%] h-[80%] w-[80%] text-white"
    />
  </span>
  <CombiModal
    :open="open"
    :weekID="weekID"
    :dayID="dayID"
    :meals="day.meals"
    @closeCombiModal="closeCombiModal"
  />
  <TransitionRoot
    :show="openPopover"
    enter="transition-opacity ease-linear duration-300"
    enter-from="opacity-0"
    enter-to="opacity-100"
    leave="transition-opacity ease-linear duration-300"
    leave-from="opacity-100"
    leave-to="opacity-0"
  >
    <OfferPopover />
  </TransitionRoot>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useJoinMeal } from '@/api/postJoinMeal'
import { useLeaveMeal } from '@/api/deleteLeaveMeal'
import { useOfferMeal } from '@/api/postOfferMeal'
import { useCancelOffer } from '@/api/postCancelOffer'
import { dashboardStore } from '@/stores/dashboardStore'
import { CheckIcon } from '@heroicons/vue/solid'
import CombiModal from '@/components/dashboard/CombiModal.vue'
import useEventsBus from "tools/eventBus";
import {TransitionRoot} from "@headlessui/vue";
import OfferPopover from "@/components/dashboard/OfferPopover.vue";
import { Day, Meal } from '@/api/getDashboardData'
import useFlashMessage from '@/services/useFlashMessage'
import { isMessage } from '@/interfaces/IMessage'
import { FlashMessageType } from '@/enums/FlashMessage'

const props = defineProps<{
  weekID: number | string,
  dayID: number | string,
  mealID: number | string,
  variationID?: number | string | null,
  meal: Meal,
  day: Day
}>();
const props = defineProps(['weekID', 'dayID', 'mealID', 'variationID', 'meal', 'day'])
const { sendFlashMessage } = useFlashMessage();

const day = props.day ? props.day : dashboardStore.getDay(props.weekID, props.dayID)
let meal: Meal;
let mealId: number | string;
if (props.variationID) {
  meal = props.meal ? props.meal : dashboardStore.getVariation(props.weekID, props.dayID, props.mealID, props.variationID)
  mealId = props.variationID
} else {
  meal = props.meal ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)
  mealId = props.mealID
}

const open = ref(false)
const isParticipating = computed(() => meal.isParticipating !== null)
const isCombiBox = props.day.meals[props.mealID].dishSlug === 'combined-dish'

const openPopover = ref(false)
const { receive } = useEventsBus()

receive("openOfferPanel_" + props.mealID, () => {
  openPopover.value = true
  setTimeout(() => openPopover.value = false, 3500)
})

const checkboxCSS = computed(() => {
  let cssResult = 'rounded-md h-[30px] w-[30px] xl:h-[20px] xl:w-[20px] '

  if (isParticipating.value === true) {
    switch (meal.mealState) {
      case 'disabled':
        cssResult += 'bg-[#B4C1CE] border-[0.5px] border-[#ABABAB]'
        return cssResult
      case 'open':
      case 'tradeable':
        cssResult += 'bg-primary-4 hover:bg-primary-3 cursor-pointer border-0'
        return cssResult
      case 'offering':
        cssResult += 'bg-highlight cursor-pointer border-0'
      case 'offerable':
        cssResult += 'bg-primary-4 cursor-pointer border-0'
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
  // Meal is not locked
  if (meal.mealState === 'open' || meal.mealState === 'tradeable') {

    // User is participating
    if (isParticipating.value) {
      await leaveMeal()
    } else {
      let slugs = [meal.dishSlug]
      if (isCombiBox === true) {
        slugs = getDishSlugs()
        if(slugs.length === 0) return
      }
      await joinMeal(slugs)
    }
  } else {
    if (meal.mealState === 'offerable') {
      await sendOffer()
    } else if (meal.mealState === 'offering') {
      await cancelOffer()
    }
  }
}

function getDishSlugs() {
  let slugs = []
  for (let mealID in day.meals) {
    if (day.meals[mealID].variations) {
      open.value = true
      return []
    } else {
      if (day.meals[mealID].dishSlug !== 'combined-dish') {
        slugs.push(day.meals[mealID].dishSlug)
      }
    }
  }

  return slugs
}

async function joinMeal(dishSlugs) {
  // is a guest component
  if (!props.dayID) {
    const { emit } = useEventsBus()
    if (isCombiBox) emit('guestChosenCombi', dishSlugs)
    emit('guestChosenMeals', mealId)
    meal.isParticipating = -1
  } else {
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
    } else {
      if (isMessage(response.value) === true) {
        sendFlashMessage({
          type: FlashMessageType.ERROR,
          message: response.value.message
        });
      }
    }
  }
}

async function leaveMeal() {
  // is a guest component
  if (!props.dayID) {
    const { emit } = useEventsBus()
    const dishSlugs = getDishSlugs()
    if (isCombiBox) emit('guestChosenCombi', dishSlugs)
    emit('guestChosenMeals', props.mealID)
    meal.isParticipating = null
  } else {
    let data = {
      mealId: mealId
    }

    const {response, error} = await useLeaveMeal(JSON.stringify(data))
    if (error.value === false) {
      day.activeSlot = response.value.slotId
      meal.isParticipating = null
    }
  }
}

async function sendOffer() {
  let data = {
    mealId: mealId
  }

  const { error } = await useOfferMeal(JSON.stringify(data))
  if (error.value === false) {
    meal.mealState = 'offering'
    const { emit } = useEventsBus()
    emit('openOfferPanel_' + mealId)
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