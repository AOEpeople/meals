<template>
  <span
    class="print:hidden"
    :class="checkboxCSS"
    data-cy="mealCheckbox"
    @click="handle"
  >
    <CheckIcon
      v-if="isParticipating"
      class="relative left-[10%] top-[10%] size-4/5 text-white"
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
import { computed, ref } from 'vue';
import { type JoinMeal, useJoinMeal } from '@/api/postJoinMeal';
import { useLeaveMeal } from '@/api/deleteLeaveMeal';
import { useOfferMeal } from '@/api/postOfferMeal';
import { useCancelOffer } from '@/api/deleteCancelOffer';
import { dashboardStore } from '@/stores/dashboardStore';
import { CheckIcon } from '@heroicons/vue/solid';
import CombiModal from '@/components/dashboard/CombiModal.vue';
import useEventsBus from '@/tools/eventBus';
import { TransitionRoot } from '@headlessui/vue';
import OfferPopover from '@/components/dashboard/OfferPopover.vue';
import { type Day, type Meal } from '@/api/getDashboardData';
import useFlashMessage from '@/services/useFlashMessage';
import { type IMessage, isMessage } from '@/interfaces/IMessage';
import { FlashMessageType } from '@/enums/FlashMessage';
import { useLockRequests } from '@/services/useLockRequests';
import { MealState } from '@/enums/MealState';
import useMealState from '@/services/useMealState';

const props = defineProps<{
  weekID: number | string | undefined;
  dayID: number | string | undefined;
  mealID: number | string;
  variationID?: number | string | null;
  meal: Meal;
  day: Day;
}>();
const { sendFlashMessage } = useFlashMessage();
const { addLock, isLocked, removeLock } = useLockRequests();

const day = props.day ?? dashboardStore.getDay(props.weekID ?? 0, props.dayID ?? 0);
const mealOrVariation = ref<Meal>();
let mealId: number | string;
if (props.variationID) {
  mealOrVariation.value =
    props.meal ?? dashboardStore.getVariation(props.weekID ?? 0, props.dayID ?? 0, props.mealID, props.variationID);
  mealId = props.variationID;
} else {
  mealOrVariation.value = props.meal
    ? props.meal
    : dashboardStore.getMeal(props.weekID ?? 0, props.dayID ?? 0, props.mealID);
  mealId = props.mealID;
}

const open = ref(false);
const isParticipating = computed(() => mealOrVariation.value?.isParticipating !== null);
const isCombiBox = props.day.meals[props.mealID].dishSlug === 'combined-dish';

const openPopover = ref(false);
const { receive } = useEventsBus();
const { generateMealState } = useMealState();

receive('openOfferPanel_' + props.mealID, () => {
  openPopover.value = true;
  setTimeout(() => (openPopover.value = false), 3500);
});

const checkboxCSS = computed(() => {
  let cssResult = 'rounded-md h-[30px] w-[30px] xl:h-[20px] xl:w-[20px] ';

  if (isParticipating.value === true) {
    switch (mealOrVariation.value?.mealState) {
      case MealState.DISABLED:
        cssResult += 'border-[0.5px] border-[#ABABAB]';
        if (mealOrVariation.value.isLocked === false) {
          cssResult += ' bg-[#80909F] cursor-pointer';
        } else {
          cssResult += ' bg-[#B4C1CE]';
        }
        return cssResult;
      case MealState.OPEN:
      case MealState.TRADEABLE:
        cssResult += 'bg-primary-4 hover:bg-primary-3 cursor-pointer border-0';
        return cssResult;
      case MealState.OFFERING:
        cssResult += 'bg-highlight cursor-pointer border-0';
        return cssResult;
      case MealState.OFFERABLE:
        cssResult += 'bg-primary-4 cursor-pointer border-0';
        return cssResult;
    }
  } else if (isParticipating.value === false) {
    switch (mealOrVariation.value?.mealState) {
      case MealState.DISABLED:
        cssResult += 'bg-[#EDEDED] border-[0.5px] border-[#ABABAB]';
        return cssResult;
      case MealState.TRADEABLE:
      case MealState.OPEN:
        cssResult += 'cursor-pointer bg-[#FAFAFA] border-[0.5px] border-[#ABABAB]';
        return cssResult;
    }
  }
  return cssResult;
});

async function handle() {
  // Meal is being offered by someone to be taken over
  if (mealOrVariation.value?.hasOffers === true && mealOrVariation.value.mealState === MealState.TRADEABLE) {
    let slugs = [mealOrVariation.value.dishSlug as string];
    if (isCombiBox === true) {
      slugs = getDishSlugs();
      if (slugs.length === 0) return;
    }
    await joinMeal(slugs);
    return;
  }
  // Meal is not locked
  if (
    (mealOrVariation.value?.isLocked === false || mealOrVariation.value?.mealState === MealState.TRADEABLE) &&
    isLocked(String(props.dayID)) === false
  ) {
    addLock(String(props.dayID));
    // User is participating
    if (isParticipating.value) {
      await leaveMeal();
    } else if (mealOrVariation.value.reachedLimit === false) {
      let slugs = [mealOrVariation.value.dishSlug];
      if (isCombiBox === true) {
        slugs = getDishSlugs();
        if (slugs.length === 0) return;
      }
      await joinMeal(slugs as string[]);
    }
    removeLock(String(props.dayID));
  } else if (isLocked(String(props.dayID)) === false) {
    addLock(String(props.dayID));
    if (mealOrVariation.value?.mealState === MealState.OFFERABLE) {
      addLock(String(props.dayID));
      await sendOffer();
    } else if (mealOrVariation.value?.mealState === MealState.OFFERING) {
      addLock(String(props.dayID));
      await cancelOffer();
    }
    removeLock(String(props.dayID));
  }
}

function getDishSlugs(): string[] {
  let slugs: string[] = [];
  for (let mealID in day.meals) {
    if (day.meals[mealID].variations) {
      open.value = true;
      return [];
    } else {
      if (day.meals[mealID].dishSlug !== 'combined-dish') {
        slugs.push(day.meals[mealID].dishSlug as string);
      }
    }
  }

  return slugs;
}

async function joinMeal(dishSlugs: string[]) {
  // is a guest component
  if (!props.dayID && mealOrVariation.value) {
    const { emit } = useEventsBus();
    if (isCombiBox) emit('guestChosenCombi', dishSlugs);
    emit('guestChosenMeals', mealId);
    mealOrVariation.value.isParticipating = -1;
  } else {
    let data = {
      mealID: mealId,
      dishSlugs: dishSlugs,
      slotID: day.activeSlot
    };

    const { response, error } = await useJoinMeal(JSON.stringify(data));
    if (error.value === false && mealOrVariation.value) {
      day.activeSlot = (response.value as JoinMeal).slotId;
      mealOrVariation.value.isParticipating = (response.value as JoinMeal).participantId;
      mealOrVariation.value.mealState = generateMealState(props.meal);
    } else if (isMessage(response.value) === true) {
      sendFlashMessage({
        type: FlashMessageType.ERROR,
        message: (response.value as IMessage).message
      });
    }
  }
}

async function leaveMeal() {
  // is a guest component
  if (!props.dayID && mealOrVariation.value) {
    const { emit } = useEventsBus();
    const dishSlugs = getDishSlugs();
    if (isCombiBox) emit('guestChosenCombi', dishSlugs);
    emit('guestChosenMeals', props.mealID);
    mealOrVariation.value.isParticipating = null;
  } else {
    const data = {
      mealId: mealId
    };

    const { response, error } = await useLeaveMeal(JSON.stringify(data));
    if (error.value === false && mealOrVariation.value && response.value) {
      day.activeSlot = response.value.slotId;
      mealOrVariation.value.mealState = generateMealState(props.meal);
      mealOrVariation.value.isParticipating = null;
    }
  }
}

async function sendOffer() {
  let data = {
    mealId: mealId
  };

  const { error } = await useOfferMeal(JSON.stringify(data));
  if (error.value === false && mealOrVariation.value) {
    mealOrVariation.value.mealState = MealState.OFFERING;
    const { emit } = useEventsBus();
    emit('openOfferPanel_' + mealId);
  }
}

async function cancelOffer() {
  let data = {
    mealId: mealId
  };

  const { error } = await useCancelOffer(JSON.stringify(data));
  if (error.value === false && mealOrVariation.value) {
    mealOrVariation.value.mealState = MealState.OFFERABLE;
  }
}

async function closeCombiModal(slugs: string[]) {
  open.value = false;
  if (slugs !== undefined) {
    await joinMeal(slugs);
  }
}
</script>
