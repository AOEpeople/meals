<template>
  <div class="flex w-auto flex-row justify-around gap-4 xl:grid-cols-6">
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center break-all sm:break-words">
        <span class="text-primary uppercase tracking-[1px] text-note font-bold">
          {{ title }}
          <span v-if="meals[mealId].isNew" class="w-[36px] h-[17px] bg-highlight text-white align-text-bottom ml-1 pl-1 pr-[3px] py-[1px] leading-[16px] text-[11px] tracking-[1.5px]">
            {{ t('dashboard.new') }}
          </span>
        </span><br>
        <p v-if="description !== ''" class="m-0 font-light description text-primary">{{ description }}</p>
      </div>
    </div>
    <div class="flex flex-none basis-2/12 items-center justify-end text-align-last">
      <ParticipationCounter :meal="meals[mealId]" :mealCSS="mealCSS"/>
      <GuestCheckbox :meals="meals" :mealId="mealId" />
    </div>
  </div>
</template>

<script setup>
import ParticipationCounter from "@/components/menuCard/ParticipationCounter.vue";
import {computed} from "vue";
import { useI18n } from "vue-i18n";
import GuestCheckbox from '@/components/guest/GuestCheckbox.vue'

const props = defineProps(['meals', 'mealId'])
const { t, locale } = useI18n();

let title = computed(() => locale.value.substring(0, 2) === 'en' ? props.meals[props.mealId].title.en : props.meals[props.mealId].title.de)
let description = ''

if(props.meals[props.mealId].description !== null) {
  description = computed(() => locale.value.substring(0, 2) === 'en' ? props.meals[props.mealId].description.en : props.meals[props.mealId].description.de);
}

const mealCSS = computed(() => {
  let css = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] '
  switch (props.meals[props.mealId].mealState) {
    case 'disabled':
      css += 'bg-[#80909F]'
      return css
    case 'open':
      css += 'bg-primary-4'
      return css
  }
})

</script>
