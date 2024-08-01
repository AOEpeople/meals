<template>
  <div
    v-if="meal.variations.length > 0"
    class="flex flex-row items-stretch"
  >
    <div
      v-for="variation in meal.variations"
      :key="variation.mealId"
      class="variations-class min-h-[24px] flex-1 border-l text-center"
    >
      <Transition
        name="participations-data"
        appear
      >
        <CheckCircleIcon
          v-if="
            (bookedMeals.booked.includes(variation.mealId) && !bookedCombinedMeal) ||
            (bookedMeals.booked.includes(meal.mealId) && meal.title.en === 'Combined Dish')
          "
          class="check-circle-icon m-auto block size-6 text-primary"
        />
      </Transition>
      <Transition
        name="participations-data"
        appear
      >
        <svg
          v-if="bookedMeals.booked.includes(variation.mealId) && bookedCombinedMeal"
          xmlns="http://www.w3.org/2000/svg"
          fill-rule="evenodd"
          clip-rule="evenodd"
          viewBox="0 0 512 508.47"
          class="combined-meal m-auto block size-[20px] pt-[2px] text-primary"
        >
          <path
            fill-rule="nonzero"
            fill="currentColor"
            d="M254.23 508.47c-3.94 0-7.87-.1-11.77-.28h-1.54v-.07c-64.9-3.34-123.37-31.04-166.45-74.12C28.46 387.99 0 324.42 0 254.23c0-70.19 28.46-133.75 74.47-179.76C117.55 31.39 176.03 3.69 240.92.34V.27h1.54c3.9-.18 7.83-.27 11.77-.27l3.46.02.08-.02c70.19 0 133.75 28.46 179.76 74.47 46 46.01 74.47 109.57 74.47 179.76S483.53 387.99 437.53 434c-46.01 46.01-109.57 74.47-179.76 74.47l-.08-.03-3.46.03zm-13.31-30.56V30.56C184.33 33.87 133.4 58.17 95.79 95.79c-40.55 40.54-65.62 96.56-65.62 158.44 0 61.89 25.07 117.91 65.62 158.45 37.61 37.61 88.54 61.91 145.13 65.23z"
          />
        </svg>
      </Transition>
    </div>
  </div>
  <div
    v-else
    class="no-variations-class h-full min-h-[24px] border-l text-center"
  >
    <Transition
      name="participations-data"
      appear
    >
      <CheckCircleIcon
        v-if="
          (bookedMeals.booked.includes(meal.mealId) && !bookedCombinedMeal) ||
          (bookedMeals.booked.includes(meal.mealId) && meal.title.en === 'Combined Dish')
        "
        class="check-circle-icon m-auto block size-6 text-primary"
      />
    </Transition>
    <Transition
      name="participations-data"
      appear
    >
      <svg
        v-if="bookedMeals.booked.includes(meal.mealId) && bookedCombinedMeal && meal.title.en !== 'Combined Dish'"
        xmlns="http://www.w3.org/2000/svg"
        fill-rule="evenodd"
        clip-rule="evenodd"
        viewBox="0 0 512 508.47"
        class="combined-meal m-auto block size-[20px] pt-[2px] text-primary"
      >
        <path
          fill-rule="nonzero"
          fill="currentColor"
          d="M254.23 508.47c-3.94 0-7.87-.1-11.77-.28h-1.54v-.07c-64.9-3.34-123.37-31.04-166.45-74.12C28.46 387.99 0 324.42 0 254.23c0-70.19 28.46-133.75 74.47-179.76C117.55 31.39 176.03 3.69 240.92.34V.27h1.54c3.9-.18 7.83-.27 11.77-.27l3.46.02.08-.02c70.19 0 133.75 28.46 179.76 74.47 46 46.01 74.47 109.57 74.47 179.76S483.53 387.99 437.53 434c-46.01 46.01-109.57 74.47-179.76 74.47l-.08-.03-3.46.03zm-13.31-30.56V30.56C184.33 33.87 133.4 58.17 95.79 95.79c-40.55 40.54-65.62 96.56-65.62 158.44 0 61.89 25.07 117.91 65.62 158.45 37.61 37.61 88.54 61.91 145.13 65.23z"
        />
      </svg>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import type { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import { CheckCircleIcon } from '@heroicons/vue/solid';

defineProps<{
  bookedMeals: IBookedData;
  meal: IMealWithVariations;
  bookedCombinedMeal: boolean;
}>();
</script>

<style>
.participations-data-enter-active,
.participations-data-leave-active {
  transition: opacity 0.5s ease;
}

.participations-data-enter-from,
.participations-data-leave-to {
  opacity: 0;
}
</style>
