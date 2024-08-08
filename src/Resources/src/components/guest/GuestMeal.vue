<template>
  <div class="flex w-auto flex-row justify-around gap-4 xl:grid-cols-6">
    <div class="basis-10/12 items-center self-center xl:col-span-5">
      <div class="self-center break-all sm:break-words">
        <span
          class="flex flex-row gap-1 break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 min-[380px]:text-note"
        >
          {{ title }}
          <VeggiIcon
            v-if="meal.diet && meal.diet !== Diet.MEAT"
            class="aspect-square"
            :diet="meal.diet"
            :class="meal.diet === Diet.VEGAN ? 'h-[17px]' : 'ml-[2px] h-[14px]'"
          />
          <span
            v-if="meal.isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-px pl-1 pr-[3px] align-text-bottom text-[11px] leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span>
        </span>
        <p
          v-if="description || description !== ''"
          class="m-0 break-words text-[12px] font-light leading-[20px] text-primary min-[380px]:text-[14px]"
        >
          {{ description }}
        </p>
      </div>
    </div>
    <div class="text-align-last flex flex-auto basis-1/12 flex-row justify-end gap-1 min-[380px]:items-center">
      <ParticipationCounter
        :meal="meal.limit"
        :mealCSS="mealCSS"
      >
        {{ participationDisplayString }}
      </ParticipationCounter>
      <GuestCheckbox
        :meals="meals"
        :mealId="mealId"
        :chosen-meals="chosenMeals"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import ParticipationCounter from '@/components/menuCard/ParticipationCounter.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import GuestCheckbox from '@/components/guest/GuestCheckbox.vue';
import { type Dictionary } from '@/types/types';
import { type Meal } from '@/api/getDashboardData';
import useMealState from '@/services/useMealState';
import VeggiIcon from '@/components/misc/VeggiIcon.vue';
import { Diet } from '@/enums/Diet';
import { MealState } from '@/enums/MealState';

const props = defineProps<{
  meals: Dictionary<Meal>;
  mealId: number | string;
  chosenMeals: string[];
}>();

const { t, locale } = useI18n();
const { generateMealState } = useMealState();

const meal = computed(() => {
  const mealForGuest = props.meals[props.mealId];
  mealForGuest.mealState = generateMealState(mealForGuest);
  return mealForGuest;
});

const title = computed(() => (locale.value.substring(0, 2) === 'en' ? meal.value.title.en : meal.value.title.de));

const description = computed(() => {
  if (meal.value?.description !== null && meal.value?.description !== undefined) {
    if (locale.value.substring(0, 2) === 'en') {
      return meal.value.description.en;
    }
    return meal.value.description.de;
  }
  return '';
});

const mealCSS = computed(() => {
  let css = 'flex content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] ';
  switch (meal.value.mealState) {
    case MealState.OFFERABLE:
    case MealState.DISABLED:
      css += 'bg-[#80909F]';
      return css;
    case MealState.OPEN:
      css += 'bg-primary-4';
      return css;
    default:
      return css;
  }
});

const participationDisplayString = computed(() => {
  const fixedCount = Math.ceil(parseFloat((meal.value.participations ?? 0).toFixed(1)));
  return (meal.value.limit ?? 0) > 0 ? `${fixedCount}/${meal.value.limit}` : fixedCount;
});
</script>
