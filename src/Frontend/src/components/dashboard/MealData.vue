<template>
  <div
    class="flex w-auto justify-between gap-1 min-[380px]:flex-row min-[380px]:gap-2 xl:grid-cols-6"
    :class="meal.dishSlug === 'combined-dish' ? 'flex-row' : 'flex-col'"
  >
    <div class="basis-11/12 items-center self-start min-[380px]:self-center xl:col-span-5">
      <div class="self-center">
        <span
          class="inline-block break-words text-[12px] font-bold leading-[20px] tracking-[0.5px] text-primary-1 min-[380px]:text-note"
        >
          {{ title }}
          <span
            v-if="meal.isNew"
            class="ml-1 h-[17px] w-[36px] bg-highlight py-px pl-1 pr-[3px] align-text-bottom text-[11px] uppercase leading-[16px] tracking-[1.5px] text-white"
          >
            {{ t('dashboard.new') }}
          </span> </span
        ><br />
        <p
          v-if="description !== null && description !== undefined && description !== ''"
          class="m-0 break-words text-[12px] font-light leading-[20px] text-primary min-[380px]:text-[14px]"
        >
          {{ description }}
        </p>
        <p
          v-if="combiDescription.length > 0 && meal.dishSlug === 'combined-dish'"
          class="m-0 break-words text-[12px] font-light leading-[20px] text-primary min-[380px]:text-[14px]"
        >
          {{ combiDescription.join(' - ') }}
        </p>
      </div>
    </div>
    <div class="text-align-last flex flex-auto basis-1/12 flex-row justify-end gap-1 min-[380px]:items-center">
      <PriceTag
        class="align-center my-auto flex print:hidden"
        :price="meal.price"
      />
      <ParticipationCounter
        :mealCSS="mealCSS"
        :limit="meal.limit"
      >
        {{ participationDisplayString }}
      </ParticipationCounter>
      <MealCheckbox
        :weekID="weekID"
        :dayID="dayID"
        :mealID="mealID"
        :meal="meal"
        :day="day"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import ParticipationCounter from '@/components/menuCard/ParticipationCounter.vue';
import MealCheckbox from '@/components/dashboard/MealCheckbox.vue';
import { useI18n } from 'vue-i18n';
import { computed, onMounted, ref, watch } from 'vue';
import { dashboardStore } from '@/stores/dashboardStore';
import PriceTag from '@/components/dashboard/PriceTag.vue';
import { Day, Meal } from '@/api/getDashboardData';
import { useDishes } from '@/stores/dishesStore';

const props = defineProps<{
  weekID: number | string | undefined;
  dayID: number | string | undefined;
  mealID: number | string;
  meal: Meal;
  day: Day;
}>();

const meal = props.meal ? props.meal : dashboardStore.getMeal(props.weekID, props.dayID, props.mealID);

const { t, locale } = useI18n();
const { getCombiDishes } = useDishes();

const title = computed(() => (locale.value.substring(0, 2) === 'en' ? meal.title.en : meal.title.de));

const description = computed(() => (locale.value.substring(0, 2) === 'en' ? meal.description.en : meal.description.de));
const combiDescription = ref<string[]>([]);

onMounted(async () => {
  if (combiDescription.value.length < 1) {
    combiDescription.value = await getCombiDescription();
  }
});

watch(
  () => meal.isParticipating,
  async () => (combiDescription.value = await getCombiDescription())
);

const mealCSS = computed(() => {
  let css = 'flex content-center rounded-md h-[30px] xl:h-[20px] ';
  switch (meal.mealState) {
    case 'disabled':
    case 'offerable':
      css += 'bg-[#80909F]';
      return css;
    case 'open':
      css += 'bg-primary-4';
      return css;
    case 'tradeable':
    case 'offering':
      css += 'bg-highlight';
      return css;
    default:
      return css;
  }
});

const participationDisplayString = computed(() => {
  const fixedCount = Math.ceil(parseFloat(meal.participations.toFixed(1)));
  return meal.limit > 0 ? `${fixedCount}/${meal.limit}` : fixedCount;
});

async function getCombiDescription() {
  if (props.meal.isParticipating !== null && props.meal.dishSlug === 'combined-dish' && dayHasVariations()) {
    const combiDishes = await getCombiDishes(typeof props.mealID === 'string' ? parseInt(props.mealID) : props.mealID);
    return combiDishes.map((dish) => (locale.value === 'de' ? dish.titleDe : dish.titleEn));
  } else {
    return [];
  }
}

function dayHasVariations() {
  for (const meal of Object.values(props.day.meals)) {
    if ((meal as Meal).variations && Object.values((meal as Meal).variations).length > 0) {
      return true;
    }
  }
  return false;
}
</script>

<style scoped>
.text-align-last {
  text-align-last: center;
}
</style>
