<template>
  <tr>
    <slot
      name="firstCell"
    />
    <template
      v-for="menuDayId, menuIndex in Object.keys(menuParticipationsState.days)"
      :key="`${menuIndex}-${menuDayId}`"
    >
      <slot
        v-if="Object.keys(getDayByWeekIdAndDayId(weekId, menuDayId).meals).length > 0"
        name="dayMeals"
        v-bind="{ dayId: menuDayId, meals: getArrayFromDict(getDayByWeekIdAndDayId(weekId, menuDayId).meals) }"
      />
    </template>
  </tr>
</template>

<script setup lang="ts">
import { useParticipations } from '@/stores/participationsStore';
import { SimpleMeal, useWeeks } from '@/stores/weeksStore';
import { Dictionary } from 'types/types';

const props = defineProps<{
  weekId: number
}>();

const { menuParticipationsState } = useParticipations(props.weekId);
const { getDayByWeekIdAndDayId } = useWeeks();

function getArrayFromDict(dict: Dictionary<SimpleMeal[]>) {
  const meals = Object.values(dict).reduce((outputArr, mealArr) => [...outputArr, ...mealArr], []);
  return meals;
}
</script>