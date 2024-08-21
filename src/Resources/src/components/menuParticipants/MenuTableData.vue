<template>
  <td
    class="z-0 h-10 border-b-2 border-r-2 border-solid border-gray-200 text-center"
    :class="{ 'cursor-pointer hover:bg-white hover:shadow-tb': edit }"
  >
    <div
      class="flex size-full"
      @click="handleClick"
    >
      <span
        v-if="bookedMeal === true"
        class="flex flex-1 place-content-center items-center"
      >
        <CheckCircleIcon class="block size-6 text-primary" />
      </span>
      <span
        v-if="bookedCombi === true && isCombi === false"
        class="flex flex-1 place-content-center items-center"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill-rule="evenodd"
          clip-rule="evenodd"
          viewBox="0 0 512 508.47"
          class="block size-[22px] text-primary"
        >
          <path
            fill-rule="nonzero"
            fill="currentColor"
            d="M254.23 508.47c-3.94 0-7.87-.1-11.77-.28h-1.54v-.07c-64.9-3.34-123.37-31.04-166.45-74.12C28.46 387.99 0 324.42 0 254.23c0-70.19 28.46-133.75 74.47-179.76C117.55 31.39 176.03 3.69 240.92.34V.27h1.54c3.9-.18 7.83-.27 11.77-.27l3.46.02.08-.02c70.19 0 133.75 28.46 179.76 74.47 46 46.01 74.47 109.57 74.47 179.76S483.53 387.99 437.53 434c-46.01 46.01-109.57 74.47-179.76 74.47l-.08-.03-3.46.03zm-13.31-30.56V30.56C184.33 33.87 133.4 58.17 95.79 95.79c-40.55 40.54-65.62 96.56-65.62 158.44 0 61.89 25.07 117.91 65.62 158.45 37.61 37.61 88.54 61.91 145.13 65.23z"
          />
        </svg>
      </span>
      <CombiDialog
        v-if="edit === true && isCombi === true"
        :openCombi="openCombi"
        :meal-id="meal.id"
        :day-id="dayId"
        :week-id="weekId"
        @close-dialog="closeCombiModal"
      />
    </div>
  </td>
</template>

<script setup lang="ts">
import { type SimpleMeal } from '@/stores/weeksStore';
import { useMealIdToDishId } from '@/services/useMealIdToDishId';
import { useParticipations } from '@/stores/participationsStore';
import { useDishes } from '@/stores/dishesStore';
import { CheckCircleIcon } from '@heroicons/vue/solid';
import CombiDialog from './CombiDialog.vue';
import { computed, ref } from 'vue';

const openCombi = ref<number | null>(null);

const props = defineProps<{
  edit: boolean;
  participant: string;
  dayId: string;
  weekId: number;
  meal: SimpleMeal;
}>();

const { mealIdToDishIdDict } = useMealIdToDishId(props.weekId);
const { addParticipantToMeal, removeParticipantFromMeal, hasParticipantBookedMeal, hasParticipantBookedCombiDish } =
  useParticipations(props.weekId);
const { getDishById } = useDishes();

const isCombi = computed(() => props.meal.dish === 'combined-dish');
const bookedCombi = computed(() => {
  const dishId = mealIdToDishIdDict.get(props.meal.id);
  if (dishId === undefined || dishId === null) return undefined;
  return hasParticipantBookedCombiDish(props.dayId, cleanParticipantName(props.participant), dishId);
});
const bookedMeal = computed(() =>
  hasParticipantBookedMeal(props.dayId, cleanParticipantName(props.participant), props.meal.id)
);

function cleanParticipantName(name: string): string {
  return name.replace(/\s\(gast\)\s*/i, ' (Guest)');
}

function handleClick() {
  if (props.edit === true && bookedMeal.value === true) {
    removeParticipantFromMeal(props.meal.id, props.participant, props.dayId);
  } else if (props.edit === true && bookedMeal.value === false) {
    addParticipantOrOpenCombi(props.meal, props.participant, props.dayId);
  }
}

function addParticipantOrOpenCombi(meal: SimpleMeal, participant: string, dayId: string) {
  if (props.edit === true && isCombi.value === false) {
    addParticipantToMeal(meal.id, participant, dayId);
  } else if (props.edit === true && isCombi.value === true) {
    openCombi.value = meal.id;
  }
}

async function closeCombiModal(combiMeals: number[]) {
  openCombi.value = null;
  if (combiMeals !== undefined && combiMeals.length === 2) {
    const dishSlugs = combiMeals
      .map((mealId) => {
        const dishId = mealIdToDishIdDict.get(mealId);
        return dishId !== -1 && typeof dishId === 'number' ? getDishById(dishId)?.slug : null;
      })
      .filter((slug) => slug !== null && slug !== undefined);
    if (dishSlugs !== null && dishSlugs !== undefined) {
      await addParticipantToMeal(props.meal.id, props.participant, props.dayId, dishSlugs as string[]);
    }
  }
}
</script>
