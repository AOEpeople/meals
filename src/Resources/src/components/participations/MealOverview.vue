<template>
  <table
    id="mealsOverview"
    ref="mealsOverview"
    class="w-full table-fixed"
  >
    <tr class="pt-10">
      <td class="h-full w-full p-1 align-top">
        <MealsSummary
          v-if="nextThreeDaysArr.length > 0"
          :day="nextThreeDaysArr[0]"
        />
      </td>
      <td class="h-full w-full p-1 align-top">
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
import { computed, onMounted, onUpdated, ref, watch } from 'vue';
import MealsSummary from './MealsSummary.vue';
import { dashboardStore } from '@/stores/dashboardStore';
import { Day } from '@/api/getDashboardData';
import { getShowParticipations } from '@/api/getShowParticipations';
import { useComponentHeights } from '@/services/useComponentHeights';

const { getCurrentDay, loadedState } = getShowParticipations();
const { setMealOverviewHeight, windowWidth } = useComponentHeights();

const mealsOverview = ref<HTMLTableElement | null>(null);
const nextThreeDaysArr = ref<Day[]>([]);
const dashboardStoreLoaded = ref<boolean>(false);
const loadingFinished = computed(() => dashboardStoreLoaded.value && loadedState.loaded);

onMounted(async () => {
  await dashboardStore.fillStore();
  dashboardStoreLoaded.value = true;
  if(mealsOverview.value) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
});

watch(loadingFinished, () => {
  nextThreeDaysArr.value = dashboardStore.getNextThreeDays(getCurrentDay());
});

watch(windowWidth, () => {
  if(mealsOverview.value) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
})

onUpdated(() => {
  if(mealsOverview.value) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
});
</script>