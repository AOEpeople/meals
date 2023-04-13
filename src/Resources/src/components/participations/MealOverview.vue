<template>
  <table class="w-full table-fixed border-t-2">
    <tr class="pt-10">
      <td class="h-full w-full border-r-2 p-1 align-top">
        <MealsSummary
          v-if="nextThreeDaysArr.length > 0"
          :day="nextThreeDaysArr[0]"
          class="border-r-2"
        />
      </td>
      <td class="h-full w-full border-r-2 p-1 align-top">
        <MealsSummary
          v-if="nextThreeDaysArr.length > 1"
          :day="nextThreeDaysArr[1]"
          class="border-r-2"
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
import { onMounted, ref } from 'vue';
import MealsSummary from './MealsSummary.vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { Day } from '@/api/getDashboardData';
import { getShowParticipations } from '@/api/getShowParticipations';

const { getCurrentDay } = getShowParticipations();
const nextThreeDaysArr = ref<Day[]>([]);

onMounted(async () => {
  // TODO: fehleranfällig, durch eigenen fetch ersetzen (läd manchmal nicht)
  await dashboardStore.fillStore();
  nextThreeDaysArr.value = dashboardStore.getNextThreeDays(getCurrentDay());
});
</script>