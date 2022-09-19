<template>
  <div class="flex flex-row gap-4 justify-around w-auto xl:grid-cols-6">
    <div class="items-center self-center basis-10/12 xl:col-span-5">
      <div class="self-center break-all sm:break-words">
        <span class="text-primary uppercase tracking-[1px] text-note font-bold">
          {{ title }}
          <span v-if="meal.isNew" class="w-[36px] h-[17px] bg-highlight text-white align-text-bottom ml-1 pl-1 pr-[3px] py-[1px] leading-[16px] text-[11px] tracking-[1.5px]">
            {{ t('dashboard.new') }}
          </span>
        </span><br>
        <p v-if="description !== ''" class="m-0 font-light description text-primary">{{ description }}</p>
      </div>
    </div>
    <div class="flex flex-none justify-end items-center basis-2/12 text-align-last">
      <div id="test" :class="
        [meal.limit > 9 ? 'w-[65px]' : 'w-[46px]', mealCSS]
      ">
        <Icons icon="person" box="0 0 12 12" class="fill-white w-3 h-3 ml-[7px] my-auto"/>
        <span class="text-white h-4 w-[15px] self-center leading-4 font-bold text-[11px] my-0.5 mr-[7px] tracking-[1.5px]">
          {{ meal.participations + [meal.limit > 0 ? '/' + meal.limit : ''] }}
        </span>
      </div>
      <Checkbox id="checkbox"
          :mealState="mealState"
          :weekID="weekID"
          :dayID="dayID"
          :mealID="mealID"
      />
    </div>
  </div>
</template>

<script setup>
import Icons from "@/components/misc/Icons.vue";
import Checkbox from '@/components/dashboard/Checkbox.vue'
import { useI18n } from "vue-i18n";
import {computed, ref} from "vue";
import {dashboardStore} from "@/store/dashboardStore";

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

const mealState = computed(() => {
  if(meal.isLocked && meal.isOpen) {
    if (meal.offerStatus === true) {
      return 'offering'
    } else if (meal.isParticipating === true && meal.offerStatus === false) {
      return 'offerable'
    } else if (meal.isParticipating === false && meal.currentOfferCount > 0) {
      return 'tradeable'
    }
  } else if(!meal.isLocked && meal.isOpen && !meal.reachedLimit) {
    return 'open'
  }
  return 'disabled'
});

const mealCSS = computed(() => {
  let css = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] '
  switch (mealState.value) {
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
  }
})

</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>