<template>
  <table>
    <thead>
      <tr>
        <th
          colspan="1"
          class="border-x-2 border-solid border-gray-200 p-2 text-center"
        >
          {{ dateRangeStr }}
        </th>
        <th
          v-for="menuDayId in Object.keys(menuParticipationsState.days)"
          :key="menuDayId"
          :colspan="getColspanFromMeals(menuDayId)"
          class="border-x-2 border-solid border-gray-200 p-2 text-center"
        >
          {{ new Date(getDayByWeekIdAndDayId(weekId, menuDayId).dateTime.date).toLocaleDateString(locale, { weekday: 'long' }) }}
        </th>
      </tr>
      <tr>
        <th
          colspan="1"
          class="border-x-2 border-solid border-gray-200 p-2 text-center"
        />
        <template
          v-for="menuDayId, menuIndex in Object.keys(menuParticipationsState.days)"
          :key="`${menuIndex}_${menuDayId}`"
        >
          <template
            v-for="mealArr, mealArrKey, mealArrIndex in getDayByWeekIdAndDayId(weekId, menuDayId).meals"
            :key="`${mealArrKey}-${mealArrIndex}`"
          >
            <th
              v-for="meal, mealIndex in mealArr"
              :key="`${meal.id}_${mealIndex}`"
              class="border-x-2 border-solid border-gray-200 px-2 text-center"
            >
              <span>{{ locale === 'en' ? getDishBySlug(meal.dish).titleEn : getDishBySlug(meal.dish).titleDe }}</span>
            </th>
          </template>
        </template>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="participant in getParticipants()"
        :key="participant"
      >
        <td
          class="border-x-2 border-solid border-gray-200 p-2 text-center"
        >
          {{ participant }}
        </td>
        <template
          v-for="menuDayId, menuIndex in Object.keys(menuParticipationsState.days)"
          :key="`${menuIndex}/${menuDayId}`"
        >
          <template
            v-for="mealArr, mealArrKey, mealArrIndex in getDayByWeekIdAndDayId(weekId, menuDayId).meals"
            :key="`${mealArrKey}+${mealArrIndex}`"
          >
            <td
              v-for="meal, mealIndex in mealArr"
              :key="`${meal.id}.${mealIndex}`"
              class="border-x-2 border-solid border-gray-200 p-2 text-center"
            >
              <span>{{ isBooked(menuDayId, participant, meal.id) === true ? 'X' : '' }}</span>
            </td>
          </template>
        </template>
      </tr>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { useWeeks } from '@/stores/weeksStore';
import { useParticipations } from '@/stores/participationsStore';
import { useDishes } from '@/stores/dishesStore';
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useMealIdToDishId } from '@/services/useMealIdToDishId';

const props = defineProps<{
  weekId: number
}>();

const { locale } = useI18n();
const { menuParticipationsState, fetchParticipations, getParticipants } = useParticipations(props.weekId);
const { getDayByWeekIdAndDayId, getWeekById, isWeek, fetchWeeks, getDateRangeOfWeek } = useWeeks();
const { getDishBySlug, DishesState, fetchDishes } = useDishes();
const { mealIdToDishIdDict } = useMealIdToDishId(props.weekId);

onMounted(async () => {
  if (isWeek(getWeekById(props.weekId)) === false) {
    await fetchWeeks();
  }
  if (DishesState.dishes.length === 0) {
    await fetchDishes();
  }

  await fetchParticipations();
});

// TODO: used very often -> cache results per day
function getColspanFromMeals(dayId: string) {
  let mealCount = 0;
  for(const mealArr of Object.values(getDayByWeekIdAndDayId(props.weekId, dayId).meals)) {
    mealArr.forEach(() => mealCount++);
  }
  return mealCount;
}

const dateRangeStr = computed(() => {
  const week = getWeekById(props.weekId);
  if (isWeek(week) === true) {
    return (
      getDateRangeOfWeek(week.calendarWeek, week.year)
        .map(date => date.toLocaleDateString(locale.value, { day: 'numeric', month: 'numeric' }))
        .join('-')
    );
  }
  return 'invalid date'
});

function isBooked(dayId: string, participant: string, mealId: number) {
  const dishId = mealIdToDishIdDict.get(mealId);
  // console.log(`Day: ${dayId}, Participant: ${participant}, MealId: ${mealId} => DishId: ${dishId}`);
  return menuParticipationsState.days[dayId][participant]?.booked.includes(dishId);
}
</script>