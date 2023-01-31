<template>
  <div class="flex w-auto flex-row justify-around gap-4 xl:grid-cols-6">
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center">
        <span class="inline-block break-words text-note font-bold leading-[20px] tracking-[0.5px] text-primary">
          {{ title }}
          <span
            v-if="meal.isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-[1px] pl-1 pr-[3px] align-text-bottom text-[11px] uppercase leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span>
        </span><br>
        <p
          v-if="description !== ''"
          class="description m-0 break-words font-light text-primary"
        >
          {{ description }}
        </p>
      </div>
    </div>
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
    <div
      class="
          text-align-last
          flex
          flex-none
          basis-2
          items-center
          justify-end"
    >
      <ParticipationCounter
        :meal="meal"
        :mealCSS="mealCSS"
      />
      <Checkbox
        :weekID="weekID"
        :dayID="dayID"
        :mealID="mealID"
        :meal="meal"
        :day="day"
      />
    </div>
  </div>
</template>

<script setup>
import ParticipationCounter from "@/components/menuCard/ParticipationCounter.vue";
import Checkbox from '@/components/dashboard/Checkbox.vue'
import {useI18n} from "vue-i18n";
import {computed, ref} from "vue";
import {dashboardStore} from "@/stores/dashboardStore";
import useEventsBus from "tools/eventBus.ts"
import OfferPopover from "@/components/dashboard/OfferPopover.vue";
import {TransitionRoot} from "@headlessui/vue";

const { receive } = useEventsBus()

const props = defineProps([
  'weekID',
  'dayID',
  'mealID',
  'meal',
  'day'
])

const meal = props.meal ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)

const { t, locale } = useI18n();

let title = computed(() => locale.value.substring(0, 2) === 'en' ? meal.title.en : meal.title.de);

let description = ''
if(meal.description !== null) {
  description = computed(() => locale.value.substring(0, 2) === 'en' ? meal.description.en : meal.description.de);
}

const mealCSS = computed(() => {
  let css = 'flex content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] '
  switch (meal.mealState) {
    case 'disabled':
    case 'offerable':
      css += 'bg-[#80909F]'
      return css
    case 'open':
      css += 'bg-primary-4'
      return css
    case 'tradeable':
    case 'offering':
      css += 'bg-highlight'
      return css
    default:
      return css
  }
})

const openPopover = ref(false)

receive("openOfferPanel_" + props.mealID, () => {
  openPopover.value = true
  setTimeout(() => openPopover.value = false, 5000)
})
</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>