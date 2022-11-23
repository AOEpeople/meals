<template>
  <div class="mb-1">
    <span class="inline-block text-primary uppercase tracking-[0.5px] leading-[20px] break-words text-note font-bold">{{ parentTitle }}</span><br>
  </div>
  <div
    v-for="(variation, variationID) in meal.variations"
    class="flex flex-row w-auto gap-4 mb-1.5 last:mb-0 xl:grid-cols-6 justify-around"
  >
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center">
        <p class="m-0 font-light description text-primary">
          {{ locale.substring(0, 2) === 'en' ? variation.title.en : variation.title.de }}
          <span
            v-if="variation.isNew"
            class="w-[36px] h-[17px] uppercase font-bold bg-highlight text-white align-text-bottom ml-1 pl-1 pr-[3px] py-[1px] leading-[16px] text-[11px] tracking-[1.5px]"
          >
            {{ t('dashboard.new') }}
          </span> 
        </p>
      </div>
    </div>
    <div class="flex flex-none basis-2/12 items-center justify-end text-align-last">
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
import { computed, ref } from 'vue'
import {dashboardStore} from "@/store/dashboardStore";

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