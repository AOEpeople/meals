<template>
  <div class="flex w-auto flex-row justify-around gap-4 xl:grid-cols-6">
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center">
        <span class="inline-block text-primary uppercase tracking-[0.5px] leading-[20px] text-note font-bold break-words">
          {{ title }}
          <span
            v-if="meal.isNew"
            class="w-[36px] h-[17px] bg-highlight text-white align-text-bottom ml-1 pl-1 pr-[3px] py-[1px] leading-[16px] text-[11px] tracking-[1.5px]"
          >
            {{ t('dashboard.new') }}
          </span>
        </span><br>
        <p
          v-if="description !== ''"
          class="m-0 break-words font-light description text-primary"
        >
          {{ description }}
        </p>
      </div>
    </div>
    <div class="flex flex-none basis-2/12 items-center justify-end text-align-last">
      <ParticipationCounter
        :meal="meal"
        :mealCSS="mealCSS"
      />
      <Checkbox
        :weekID="weekID"
        :dayID="dayID"
        :mealID="mealID"
      />
    </div>
  </div>
</template>

<script setup>
import ParticipationCounter from "@/components/menuCard/ParticipationCounter.vue";
import Checkbox from '@/components/dashboard/Checkbox.vue'
import { useI18n } from "vue-i18n";
import {computed} from "vue";
import {dashboardStore} from "@/stores/dashboardStore";

const props = defineProps([
  'weekID',
  'dayID',
  'mealID',
])

const meal = dashboardStore.getMeal(props.weekID, props.dayID, props.mealID)

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

</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>