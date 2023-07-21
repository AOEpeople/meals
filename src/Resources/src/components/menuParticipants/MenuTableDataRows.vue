<template>
  <MenuTableRow
    ref="row"
    :week-id="weekId"
    :class="editRow === true ? 'bg-green' : ''"
  >
    <template #firstCell>
      <td
        class="sticky left-0 cursor-pointer whitespace-nowrap border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] p-2 text-center text-[12px] hover:bg-slate-400"
        @click="editRow = !editRow"
      >
        {{ participant }}
      </td>
    </template>
    <template #dayMeals="{ dayId, meals }">
      <td
        v-for="meal, mealIndex in meals"
        :key="`${meal.id}.${mealIndex}`"
        class="h-10 cursor-pointer border-b-2 border-r-2 border-solid border-gray-200 text-center"
      >
        <span
          v-if="isBooked(dayId, participant, meal.id) === true"
          class="flex place-content-center hover:bg-slate-400"
        >
          <svg
            v-if="meal.dish !== 'combined-dish' && isCombiBooked(dayId, participant, meals) === true"
            xmlns="http://www.w3.org/2000/svg"
            fill-rule="evenodd"
            clip-rule="evenodd"
            viewBox="0 0 512 508.47"
            class="combined-meal m-auto block h-[20px] w-[20px] pt-[2px] text-primary"
          >
            <path
              fill-rule="nonzero"
              fill="currentColor"
              d="M254.23 508.47c-3.94 0-7.87-.1-11.77-.28h-1.54v-.07c-64.9-3.34-123.37-31.04-166.45-74.12C28.46 387.99 0 324.42 0 254.23c0-70.19 28.46-133.75 74.47-179.76C117.55 31.39 176.03 3.69 240.92.34V.27h1.54c3.9-.18 7.83-.27 11.77-.27l3.46.02.08-.02c70.19 0 133.75 28.46 179.76 74.47 46 46.01 74.47 109.57 74.47 179.76S483.53 387.99 437.53 434c-46.01 46.01-109.57 74.47-179.76 74.47l-.08-.03-3.46.03zm-13.31-30.56V30.56C184.33 33.87 133.4 58.17 95.79 95.79c-40.55 40.54-65.62 96.56-65.62 158.44 0 61.89 25.07 117.91 65.62 158.45 37.61 37.61 88.54 61.91 145.13 65.23z"
            />
          </svg>
          <CheckCircleIcon
            v-else
            class="m-auto block h-6 w-6 text-primary hover:bg-slate-400"
            @click="editRow === true && meal.dish !== 'combined-dish' && removeParticipantFromMeal(meal.id, participant, dayId)"
          />
        </span>
        <span
          v-else
          class="flex h-full w-full place-content-center"
          :class="{ 'cursor-pointer hover:bg-slate-400': editRow }"
          @click="addParticipantOrOpenCombi(meal, participant, dayId)"
        >
          <CombiDialog
            v-if="editRow === true && meal.dish === 'combined-dish'"
            :openCombi="openCombi"
            :meal-id="meal.id"
            :day-id="dayId"
            :week-id="weekId"
            @close-dialog="closeCombiModal([])"
          />
        </span>
      </td>
    </template>
  </MenuTableRow>
</template>

<script setup lang="ts">
import MenuTableRow from './MenuTableRow.vue';
import { useParticipations } from '@/stores/participationsStore';
import { useMealIdToDishId } from '@/services/useMealIdToDishId';
import { SimpleMeal } from '@/stores/weeksStore';
import { CheckCircleIcon } from '@heroicons/vue/solid';
import { ref, watch } from 'vue';
import useDetectClickOutside from '@/services/useDetectClickOutside';
import CombiDialog from './CombiDialog.vue';

const editRow = ref<boolean>(false);
const row = ref<HTMLElement | null>(null);
const openCombi = ref<number | null>(null);

const props = defineProps<{
  weekId: number,
  participant: string
}>();

const { menuParticipationsState, addParticipantToMeal, removeParticipantFromMeal } = useParticipations(props.weekId);
const { mealIdToDishIdDict } = useMealIdToDishId(props.weekId);

watch(
  editRow,
  () => (editRow.value === true) && useDetectClickOutside(row, () => editRow.value = false)
);

function isBooked(dayId: string, participant: string, mealId: number) {
  const dishId = mealIdToDishIdDict.get(mealId);
  return menuParticipationsState.days[dayId][participant]?.booked.includes(dishId);
}

function getCombinedMeal(meals: SimpleMeal[]) {
  return meals.find(meal => meal.dish === 'combined-dish');
}

function isCombiBooked(dayId: string, participant: string, meals: SimpleMeal[]) {
  const combiMeal = getCombinedMeal(meals)?.id;

  if (combiMeal === undefined || combiMeal === null) {
    return false;
  }

  return isBooked(dayId, participant, combiMeal);
}

function addParticipantOrOpenCombi(meal: SimpleMeal, participant: string, dayId: string) {
  if (editRow.value === true && meal.dish !== 'combined-dish') {
    addParticipantToMeal(meal.id, participant, dayId);
  } else if (editRow.value === true && meal.dish === 'combined-dish') {
    openCombi.value = meal.id;
  }
}

function closeCombiModal(slugs: string[]) {
  openCombi.value = null;
  if (slugs !== undefined && slugs.length === 2) {
    console.log(`Combi Slugs: ${slugs.join(', ')}`);
  }
}
</script>