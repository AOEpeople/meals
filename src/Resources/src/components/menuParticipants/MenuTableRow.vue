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
  const outputArr: SimpleMeal[] = [];

  for(const mealArr of Object.values(dict)) {
    mealArr.forEach(meal => outputArr.push(meal));
  }

  return outputArr;
}
</script>