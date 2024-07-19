<template>
  <div class="flex w-auto flex-row justify-around gap-4 xl:grid-cols-6">
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center break-all sm:break-words">
        <span class="text-note font-bold uppercase tracking-[1px] text-primary">
          {{ title }}
          <span
            v-if="meals[mealId].isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-px pl-1 pr-[3px] align-text-bottom text-[11px] leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span> </span
        ><br />
        <p
          v-if="description !== ''"
          class="description m-0 font-light text-primary"
        >
          {{ description }}
        </p>
      </div>
    </div>
    <div class="text-align-last flex flex-none basis-2/12 items-center justify-end">
      <ParticipationCounter
        :meal="meals[mealId]"
        :mealCSS="mealCSS"
      />
      <GuestCheckbox
        :meals="meals"
        :mealId="mealId"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import ParticipationCounter from '@/components/menuCard/ParticipationCounter.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import GuestCheckbox from '@/components/guest/GuestCheckbox.vue';
import { Dictionary } from 'types/types';
import { Meal } from '@/api/getDashboardData';

const props = defineProps<{
  meals: Dictionary<Meal>;
  mealId: number | string;
}>();

const { t, locale } = useI18n();

const title = computed(() =>
  locale.value.substring(0, 2) === 'en' ? props.meals[props.mealId].title.en : props.meals[props.mealId].title.de
);
const description = computed(() => {
  if (props.meals[props.mealId].description !== null) {
    if (locale.value.substring(0, 2) === 'en') {
      return props.meals[props.mealId].description.en;
    }
    return props.meals[props.mealId].description.de;
  }
  return 1;
});

const mealCSS = computed(() => {
  let css = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] ';
  switch (props.meals[props.mealId].mealState) {
    case 'disabled':
      css += 'bg-[#80909F]';
      return css;
    case 'open':
      css += 'bg-primary-4';
      return css;
    default:
      return css;
  }
});
</script>
