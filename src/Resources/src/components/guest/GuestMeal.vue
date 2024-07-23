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
        :meal="meals[mealId].limit"
        :mealCSS="mealCSS"
      >
        {{ participationDisplayString }}
      </ParticipationCounter>
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
import useMealState from '@/services/useMealState';

const props = defineProps<{
  meals: Dictionary<Meal>;
  mealId: number | string;
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
  if (meal.value.description !== null) {
    if (locale.value.substring(0, 2) === 'en') {
      return meal.value.description.en;
    }
    return meal.value.description.de;
  }
  return 1;
});

const mealCSS = computed(() => {
  let css = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] ';
  switch (meal.value.mealState) {
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

const participationDisplayString = computed(() => {
  const fixedCount = Math.ceil(parseFloat(meal.value.participations.toFixed(1)));
  return meal.value.limit > 0 ? `${fixedCount}/${meal.value.limit}` : fixedCount;
});
</script>
