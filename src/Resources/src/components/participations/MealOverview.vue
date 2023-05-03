<template>
  <table
    id="mealsOverview"
    ref="mealsOverview"
    class="h-full w-full table-fixed"
  >
    <tr>
      <td class="h-full w-full pb-6 pr-4 align-top">
        <Transition
          name="summary"
          appear
          @afterEnter="onAfterEnter"
        >
          <MealsSummary
            v-if="nextThreeDaysArr.length > 0"
            :day="nextThreeDaysArr[0]"
          />
        </Transition>
      </td>
      <td class="h-full w-full pb-6 pr-4 align-top">
        <Transition
          name="summary"
          appear
        >
          <MealsSummary
            v-if="nextThreeDaysArr.length > 1"
            :day="nextThreeDaysArr[1]"
          />
        </Transition>
      </td>
      <td class="h-full w-full pb-6 align-top">
        <Transition
          name="summary"
          appear
        >
          <MealsSummary
            v-if="nextThreeDaysArr.length > 2"
            :day="nextThreeDaysArr[2]"
          />
        </Transition>
      </td>
    </tr>
  </table>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, onUpdated, ref, watch } from 'vue';
import MealsSummary from './MealsSummary.vue';
import { getDashboardData } from '@/api/getDashboardData';
import { Day } from '@/api/getDashboardData';
import { getShowParticipations } from '@/api/getShowParticipations';
import { useComponentHeights } from '@/services/useComponentHeights';

const { getCurrentDay, loadedState } = getShowParticipations();
const { setMealOverviewHeight, windowWidth } = useComponentHeights();
const { activatePeriodicFetch, disablePeriodicFetch, getNextThreeDays, getDashboard, dashBoardState } = getDashboardData();

const mealsOverview = ref<HTMLTableElement | null>(null);
const nextThreeDaysArr = ref<Day[]>([]);
const dashBoardLoaded = ref(false);
const loaded = computed(() => loadedState.loaded && dashBoardLoaded.value);

onMounted(async () => {
  await getDashboard();
  dashBoardLoaded.value = true;
  if(mealsOverview.value) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
  activatePeriodicFetch();
});

watch(
  () => dashBoardState.weeks,
  () => {
  nextThreeDaysArr.value = getNextThreeDays(getCurrentDay());
});

watch(loaded, () => nextThreeDaysArr.value = getNextThreeDays(getCurrentDay()));

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

onUnmounted(() => {
  disablePeriodicFetch();
});

function onAfterEnter(el: Element) {
  if(mealsOverview.value) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
}
</script>

<style>
.summary-enter-active,
.summary-leave-active {
  transition: all 1s ease;
}

.summary-enter-from,
.summary-leave-to {
  opacity: 0;
  transform: translateY(60px);
}
</style>