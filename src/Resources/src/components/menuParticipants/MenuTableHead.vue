<template>
  <thead>
    <tr>
      <th
        rowspan="2"
        class="border-b-2 border-r-2 border-solid border-gray-200 p-2 text-center"
      >
        {{ dateRangeStr }}
      </th>
      <th
        v-for="menuDayId in Object.keys(menuParticipationsState.days)"
        :key="menuDayId"
        :colspan="getColspanFromMeals(menuDayId)"
        class="border-x-2 border-b-2 border-solid border-gray-200 p-2 text-center"
      >
        {{ new Date(getDayByWeekIdAndDayId(weekId, menuDayId).dateTime.date).toLocaleDateString(locale, { weekday: 'long' }) }}
      </th>
    </tr>
    <MenuTableRow
      :week-id="weekId"
    >
      <template #dayMeals="{ dayId, meals }">
        <th
          v-for="meal, mealIndex in meals"
          :key="`${meal.id}_${mealIndex}`"
          class="border-2 border-solid border-gray-200 px-2 text-center"
        >
          <span>{{ locale === 'en' ? getDishBySlug(meal.dish).titleEn : getDishBySlug(meal.dish).titleDe }}</span>
        </th>
      </template>
    </MenuTableRow>
  </thead>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { useDishes } from '@/stores/dishesStore';
import { computed } from 'vue';
import { useWeeks } from '@/stores/weeksStore';
import MenuTableRow from './MenuTableRow.vue';
import { useParticipations } from '@/stores/participationsStore';

const props = defineProps<{
  weekId: number
}>();

const { locale } = useI18n();
const { getDishBySlug } = useDishes();
const { getDayByWeekIdAndDayId, getWeekById, isWeek, getDateRangeOfWeek } = useWeeks();
const { menuParticipationsState } = useParticipations(props.weekId);

function getColspanFromMeals(dayId: string) {
  let mealCount = 0;
  for(const mealArr of Object.values(getDayByWeekIdAndDayId(props.weekId, dayId)?.meals)) {
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
</script>