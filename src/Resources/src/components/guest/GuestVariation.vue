<template>
  <div>
    <span
      class="inline-block break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 min-[380px]:text-note"
      >{{ parentTitle }}</span
    >
  </div>
  <div
    v-for="(variation, variationID, index) in meal.variations"
    :key="index"
    class="mb-1.5 flex w-auto flex-col justify-around gap-1 last:mb-0 min-[380px]:flex-row min-[380px]:gap-2 xl:grid-cols-6"
  >
    <div class="basis-11/12 items-center self-start min-[380px]:self-center xl:col-span-5">
      <div class="self-center">
        <p
          class="m-0 flex flex-row content-center gap-1 text-[12px] font-light leading-5 text-primary min-[380px]:text-[14px]"
        >
          {{ locale.substring(0, 2) === 'en' ? variation.title.en : variation.title.de }}
          <VeggiIcon
            v-if="variation.diet && variation.diet !== Diet.MEAT"
            :diet="variation.diet ?? Diet.MEAT"
            :class="variation.diet && variation.diet === Diet.VEGAN ? 'h-[17px]' : 'ml-[2px] h-[14px]'"
          />
          <span
            v-if="variation.isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-px pl-1 pr-[3px] align-text-bottom text-[11px] font-bold uppercase leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span>
        </p>
      </div>
    </div>
    <div
      class="text-align-last flex flex-auto basis-1/12 flex-row justify-end gap-1 min-[380px]:flex-row min-[380px]:items-center"
    >
      <ParticipationCounter
        :limit="variation.limit ?? 0"
        :mealCSS="mealCSS.get(String(variationID)) ?? ''"
      >
        {{ getParticipationDisplayString(variation) }}
      </ParticipationCounter>
      <GuestCheckbox
        :mealId="variationID"
        :meals="{ [variationID]: variation }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import ParticipationCounter from '@/components/menuCard/ParticipationCounter.vue';
import GuestCheckbox from '@/components/guest/GuestCheckbox.vue';
import VeggiIcon from '@/components/misc/VeggiIcon.vue';
import { Diet } from '@/enums/Diet';
import { useI18n } from 'vue-i18n';
import { type Meal } from '@/api/getDashboardData';
import { computed } from 'vue';
import { MealState } from '@/enums/MealState';
import useMealState from '@/services/useMealState';

const { t, locale } = useI18n();
const { generateMealState } = useMealState();

const props = defineProps<{
  meal: Meal;
}>();

const parentTitle = computed(() => (locale.value.substring(0, 2) === 'en' ? props.meal.title.en : props.meal.title.de));

const mealCSS = computed(() => {
  const css: Map<string, string> = new Map();
  for (const [variationId, variation] of Object.entries(props.meal.variations ?? {})) {
    let cssStr = 'grid grid-cols-2 content-center rounded-md h-[30px] xl:h-[20px] mr-[15px] ';
    switch (generateMealState(variation as Meal)) {
      case MealState.OFFERABLE:
      case MealState.DISABLED:
        cssStr += 'bg-[#80909F]';
        break;
      case MealState.OPEN:
        cssStr += 'bg-primary-4';
        break;
      default:
        break;
    }
    css.set(variationId, cssStr);
  }
  return css;
});

const getParticipationDisplayString = (variation: Meal) => {
  const fixedCount = Math.ceil(parseFloat((variation.participations ?? 0).toFixed(1)));
  return (variation.limit ?? 0) > 0 ? `${fixedCount}/${variation.limit}` : fixedCount;
};
</script>
