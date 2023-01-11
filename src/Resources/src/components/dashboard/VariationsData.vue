<template>
  <div class="mb-1">
    <span class="inline-block break-words text-note font-bold uppercase leading-[20px] tracking-[0.5px] text-primary">{{ parentTitle }}</span><br>
  </div>
  <div
    v-for="(variation, variationID, index) in meal.variations"
    :key="index"
    class="mb-1.5 flex w-auto flex-row justify-around gap-4 last:mb-0 xl:grid-cols-6"
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
    <div class="text-align-last flex flex-none basis-2/12 items-center justify-end">
      <ParticipationCounter
        :meal="variation"
        :mealCSS="mealCSS[variationID]"
      />
      <Checkbox
        :weekID="weekID"
        :dayID="dayID"
        :mealID="mealID"
        :variationID="variationID"
      />
    </div>
  </div>
</template>

<script setup>
import ParticipationCounter from "@/components/menuCard/ParticipationCounter.vue";
import Checkbox from '@/components/dashboard/Checkbox.vue'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import {dashboardStore} from "@/stores/dashboardStore";

const { t, locale } = useI18n()

const props = defineProps([
    'weekID',
    'dayID',
    'mealID',
])

const meal = dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)

let parentTitle = computed(() => locale.value.substring(0, 2) === 'en' ? meal.title.en : meal.title.de)

const mealCSS = computed(() => {
  let array = []
  for (const variationId in meal.variations) {
    array[variationId] = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] '
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

</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>