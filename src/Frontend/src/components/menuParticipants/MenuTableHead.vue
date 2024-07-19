<template>
  <thead class="sticky top-0 z-20">
    <tr>
      <th
        class="sticky left-0 top-0 z-40 whitespace-nowrap border-r-2 border-solid border-gray-200 bg-[#f4f7f9] px-4 py-2 text-start"
      >
        {{ dateRangeStr }}
      </th>
      <template
        v-for="menuDayId in Object.keys(menuParticipationsState.days)"
        :key="menuDayId"
      >
        <th
          v-if="Object.keys(getDayByWeekIdAndDayId(weekId, menuDayId).meals).length > 0"
          :colspan="getColspanFromMeals(menuDayId)"
          class="sticky z-30 border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] p-2 text-center"
        >
          {{
            new Date(getDayByWeekIdAndDayId(weekId, menuDayId).dateTime.date).toLocaleDateString(locale, {
              weekday: 'long'
            })
          }}
        </th>
      </template>
    </tr>
    <MenuTableRow :week-id="weekId">
      <template #firstCell>
        <th
          class="sticky left-0 top-0 z-40 whitespace-nowrap border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] px-4 py-2 text-start"
        />
      </template>
      <template #dayMeals="{ dayId, meals }">
        <th
          v-for="(meal, mealIndex) in meals"
          :key="`${String(meal.id)}_${String(mealIndex)}`"
          class="sticky top-0 z-20 border-b-2 border-r-2 border-solid border-gray-200 bg-[#f4f7f9] px-2 text-center"
        >
          <span class="block w-[100px] hyphens-auto break-words text-[14px]">
            {{ locale === 'en' ? getDishBySlug(meal.dish)?.titleEn : getDishBySlug(meal.dish)?.titleDe }}
          </span>
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
  weekId: number;
}>();

const { locale } = useI18n();
const { getDishBySlug } = useDishes();
const { getDayByWeekIdAndDayId, getWeekById, isWeek, getDateRangeOfWeek } = useWeeks();
const { menuParticipationsState } = useParticipations(props.weekId);

function getColspanFromMeals(dayId: string) {
  let mealCount = 0;
  for (const mealArr of Object.values(getDayByWeekIdAndDayId(props.weekId, dayId)?.meals)) {
    mealArr.forEach(() => mealCount++);
  }
  return mealCount;
}

const dateRangeStr = computed(() => {
  const week = getWeekById(props.weekId);
  if (isWeek(week) === true) {
    return getDateRangeOfWeek(week.calendarWeek, week.year)
      .map((date) => date.toLocaleDateString(locale.value, { day: 'numeric', month: 'numeric' }))
      .join(' - ');
  }
  return 'invalid date';
});
</script>
