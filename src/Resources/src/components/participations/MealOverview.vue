<template>
  <table class="w-full table-fixed border-t-2">
    <tr class="pt-10">
      <td class="h-full w-full border-r-2 p-1 align-top">
        <MealsSummary
          v-if="nextThreeDaysArr.length > 0"
          :day="nextThreeDaysArr[0]"
        />
      </td>
      <td class="h-full w-full border-r-2 p-1 align-top">
        <MealsSummary
          v-if="nextThreeDaysArr.length > 1"
          :day="nextThreeDaysArr[1]"
        />
      </td>
      <td class="h-full w-full p-1 align-top">
        <MealsSummary
          v-if="nextThreeDaysArr.length > 2"
          :day="nextThreeDaysArr[2]"
        />
      </td>
    </tr>
  </table>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import MealsSummary from './MealsSummary.vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { Day } from '@/api/getDashboardData';
import { getShowParticipations } from '@/api/getShowParticipations';

const { getCurrentDay, loadedState } = getShowParticipations();
const nextThreeDaysArr = ref<Day[]>([]);

onMounted(async () => {
  await dashboardStore.fillStore();
});

watch(loadedState, () => {
  nextThreeDaysArr.value = dashboardStore.getNextThreeDays(getCurrentDay());
});
</script>