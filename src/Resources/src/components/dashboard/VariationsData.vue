<template>
  <div class="mb-1">
    <span class="inline-block break-words text-note font-bold leading-[20px] tracking-[0.5px] text-primary-1">{{ parentTitle }}</span><br>
  </div>
  <div
    v-for="(variation, variationID, index) in meal.variations"
    :key="index"
    class="mb-1.5 flex w-auto flex-row justify-around gap-2 last:mb-0 xl:grid-cols-6"
  >
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center">
        <p class="description m-0 font-light text-primary">
          {{ locale.substring(0, 2) === 'en' ? variation.title.en : variation.title.de }}
          <span
            v-if="variation.isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-[1px] pl-1 pr-[3px] align-text-bottom text-[11px] font-bold uppercase leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span> 
        </p>
      </div>
    </div>
    <transition
      enter="transition-opacity ease-linear duration-300"
      enter-from="opacity-0"
      enter-to="opacity-100"
      leave="transition-opacity ease-linear duration-300"
      leave-from="opacity-100"
      leave-to="opacity-0"
    >
      <OfferPopover v-if="openPopover" />
    </transition>
    <PriceTag
      class="align-center my-auto flex"
      :price="variation.price"
    />
    <div class="text-align-last flex flex-auto basis-2/12 items-center justify-end">
      <ParticipationCounter
        :meal="variation"
        :mealCSS="mealCSS[variationID]"
      />
      <Checkbox
        :weekID="weekID"
        :dayID="dayID"
        :mealID="mealID"
        :variationID="variationID"
        :meal="variation"
        :day="day"
      />
    </div>
  </div>
</template>

<script setup>
import ParticipationCounter from "@/components/menuCard/ParticipationCounter.vue";
import Checkbox from '@/components/dashboard/Checkbox.vue'
import {useI18n} from 'vue-i18n'
import {computed, ref} from 'vue'
import {dashboardStore} from "@/stores/dashboardStore";
import useEventsBus from "tools/eventBus.ts"
import OfferPopover from "@/components/dashboard/OfferPopover.vue";
import PriceTag from "@/components/dashboard/PriceTag.vue";

const { receive } = useEventsBus()

const { t, locale } = useI18n()

const props = defineProps([
    'weekID',
    'dayID',
    'mealID',
    'day',
    'meal'
])

const meal = props.meal ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)

let parentTitle = computed(() => locale.value.substring(0, 2) === 'en' ? meal.title.en : meal.title.de)

const mealCSS = computed(() => {
  let array = []
  for (const variationId in meal.variations) {
    array[variationId] = 'flex content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] '
    switch (meal.variations[variationId].mealState) {
      case 'disabled':
      case 'offerable':
        array[variationId] += 'bg-[#80909F]'
        break
      case 'open':
        array[variationId] += 'bg-primary-4'
        break
      case 'tradeable':
      case 'offering':
        array[variationId] += 'bg-highlight'
        break
    }
  }
  return array
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