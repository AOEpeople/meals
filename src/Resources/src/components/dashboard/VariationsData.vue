<template>
  <div class="mb-1">
    <span
      class="inline-block break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 min-[380px]:text-note"
      >{{ parentTitle }}</span
    ><br />
  </div>
  <div
    v-for="(variation, variationID, index) in meal?.variations"
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
            class="aspect-square"
            :diet="variation.diet"
            :class="variation.diet === Diet.VEGAN ? 'h-[17px]' : 'ml-[2px] h-[14px]'"
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
    <Transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <OfferPopover v-if="openPopover" />
    </Transition>
    <div
      class="text-align-last flex flex-auto basis-1/12 flex-row justify-end gap-1 min-[380px]:flex-row min-[380px]:items-center"
    >
      <PriceTag
        class="align-center my-auto flex print:hidden"
        :price="variation.price ?? 0"
      />
      <ParticipationCounter
        :limit="variation.limit ?? 0"
        :mealCSS="mealCSS.get(String(variationID)) ?? ''"
      >
        {{ getParticipationDisplayString(variation) }}
      </ParticipationCounter>
      <MealCheckbox
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

<script setup lang="ts">
import ParticipationCounter from '@/components/menuCard/ParticipationCounter.vue';
import MealCheckbox from '@/components/dashboard/MealCheckbox.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref } from 'vue';
import { dashboardStore } from '@/stores/dashboardStore';
import useEventsBus from '@/tools/eventBus';
import OfferPopover from '@/components/dashboard/OfferPopover.vue';
import PriceTag from '@/components/dashboard/PriceTag.vue';
import { type Day, type Meal } from '@/api/getDashboardData';
import VeggiIcon from '@/components/misc/VeggiIcon.vue';
import { Diet } from '@/enums/Diet';
import { MealState } from '@/enums/MealState';

const { receive } = useEventsBus();

const { t, locale } = useI18n();

const props = defineProps<{
  weekID: number | string;
  dayID: number | string;
  mealID: number | string;
  meal: Meal;
  day: Day;
}>();

const meal = props.meal ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID);

const parentTitle = computed(() => (locale.value.substring(0, 2) === 'en' ? meal?.title.en : meal?.title.de));

const mealCSS = computed(() => {
  const css: Map<string, string> = new Map();
  for (const [variationId, variation] of Object.entries(meal?.variations ?? {})) {
    let cssStr = 'flex content-center rounded-md h-[30px] xl:h-[20px] ';
    switch (variation.mealState) {
      case MealState.DISABLED:
      case MealState.OFFERABLE:
        cssStr += 'bg-[#80909F]';
        break;
      case MealState.OPEN:
        cssStr += 'bg-primary-4';
        break;
      case MealState.TRADEABLE:
      case MealState.OFFERING:
        cssStr += 'bg-highlight';
        break;
    }
    css.set(variationId, cssStr);
  }
  return css;
});

const openPopover = ref(false);

receive('openOfferPanel_' + props.mealID, () => {
  openPopover.value = true;
  setTimeout(() => (openPopover.value = false), 5000);
});

const getParticipationDisplayString = (variation: Meal) => {
  const fixedCount = Math.ceil(parseFloat((variation.participations ?? 0).toFixed(1)));
  return (variation.limit ?? 0) > 0 ? `${fixedCount}/${variation.limit}` : fixedCount;
};
</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>
