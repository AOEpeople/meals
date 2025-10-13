<template>
  <table
    id="mealsOverview"
    ref="mealsOverview"
    class="size-full table-fixed"
  >
    <tbody>
      <tr>
        <td class="size-full pb-6 pr-4 align-top">
          <Transition
            name="summary"
            appear
            @afterEnter="onAfterEnter"
          >
            <MealsSummary
              v-if="nextThreeDaysState.days.length > 0"
              :day="nextThreeDaysState.days[0] as IDay"
            />
          </Transition>
        </td>
        <td class="size-full pb-6 pr-4 align-top">
          <Transition
            name="summary"
            appear
          >
            <MealsSummary
              v-if="nextThreeDaysState.days.length > 1"
              :day="nextThreeDaysState.days[1] as IDay"
            />
          </Transition>
        </td>
        <td class="size-full pb-6 align-top">
          <Transition
            name="summary"
            appear
          >
            <MealsSummary
              v-if="nextThreeDaysState.days.length > 2"
              :day="nextThreeDaysState.days[2] as IDay"
            />
          </Transition>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, onUpdated, ref, watch } from 'vue';
import MealsSummary from './MealsSummary.vue';
import { type IDay, getNextThreeDays } from '@/api/getMealsNextThreeDays';
import { useComponentHeights } from '@/services/useComponentHeights';

const { setMealOverviewHeight, windowWidth } = useComponentHeights();
const { nextThreeDaysState, activatePeriodicFetch, disablePeriodicFetch, fetchNextThreeDays } = getNextThreeDays();

const mealsOverview = ref<HTMLTableElement | null>(null);

onMounted(async () => {
  await fetchNextThreeDays();
  if (mealsOverview.value !== null && mealsOverview.value !== undefined) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
  activatePeriodicFetch();
});

watch(windowWidth, () => {
  if (mealsOverview.value !== null && mealsOverview.value !== undefined) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
});

onUpdated(() => {
  if (mealsOverview.value !== null && mealsOverview.value !== undefined) {
    setMealOverviewHeight(mealsOverview.value.offsetHeight, 'mealsOverview');
  }
});

onUnmounted(() => {
  disablePeriodicFetch();
});

function onAfterEnter() {
  if (mealsOverview.value !== null && mealsOverview.value !== undefined) {
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
