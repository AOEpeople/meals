<template>
  <table class="w-full table-fixed">
    <tr>
      <td>
        <MealsSummary
          v-if="nextThreeDaysArr.length > 0"
          :day="nextThreeDaysArr[0]"
        />
      </td>
      <td>
        <MealsSummary
          v-if="nextThreeDaysArr.length > 1"
          :day="nextThreeDaysArr[1]"
        />
      </td>
      <td>
        <MealsSummary
          v-if="nextThreeDaysArr.length > 2"
          :day="nextThreeDaysArr[2]"
        />
      </td>
    </tr>
  </table>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import MealsSummary from './MealsSummary.vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { Day } from '@/api/getDashboardData';

const nextThreeDaysArr = ref<Day[]>([]);

onMounted(async () => {
  await dashboardStore.fillStore();
  nextThreeDaysArr.value = getNextThreeDays();
});


function getNextThreeDays(): Day[] {
  const nextThreeDays: Day[] = [];
  const weeks = dashboardStore.getWeeks();
  for(const weekKey of Object.keys(weeks)) {
    const days = dashboardStore.getDays(weekKey);
    if(days) {
      for(const [dayKey, dayValue] of Object.entries(days)) {
        if(!dayValue.isLocked) {
          console.log(`Adding day <${dayValue.date.date}> to the next three days.`);
          nextThreeDays.push(dayValue);
        }
        if(nextThreeDays.length === 3) {
          return nextThreeDays;
        }
      }
    }
  }
  return nextThreeDays;
}
</script>