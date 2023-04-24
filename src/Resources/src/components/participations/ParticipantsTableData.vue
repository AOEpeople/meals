<template>
  <div
    v-if="meal.variations.length > 0"
    class="flex flex-row items-stretch"
  >
    <div
      v-for="(variation, index) in meal.variations"
      :key="variation.mealId"
      class="min-h-[24px] flex-1 border-l-[1px] text-center"
    >
      <svg
        v-if="bookedMeals.booked.includes(variation.mealId) && !bookedCombinedMeal"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        class="text-primary m-auto block h-6 w-6"
      >
        <path
          d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm0,18a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z"
          fill="currentColor"
        />
        <path
          d="M14.7,8.39l-3.78,5L9.29,11.28a1,1,0,0,0-1.58,1.23l2.43,3.11a1,1,0,0,0,.79.38h0a1,1,0,0,0,.79-.39l4.57-6a1,1,0,1,0-1.6-1.22Z"
          fill="currentColor"
        />
      </svg>
      <svg
        v-if="bookedMeals.booked.includes(variation.mealId) && bookedCombinedMeal"
        xmlns="http://www.w3.org/2000/svg"
        fill-rule="evenodd"
        clip-rule="evenodd"
        viewBox="0 0 512 508.47"
        class="text-primary m-auto block h-6 w-6"
      >
        <path
          fill-rule="nonzero"
          fill="currentColor"
          d="M254.23 508.47c-3.94 0-7.87-.1-11.77-.28h-1.54v-.07c-64.9-3.34-123.37-31.04-166.45-74.12C28.46 387.99 0 324.42 0 254.23c0-70.19 28.46-133.75 74.47-179.76C117.55 31.39 176.03 3.69 240.92.34V.27h1.54c3.9-.18 7.83-.27 11.77-.27l3.46.02.08-.02c70.19 0 133.75 28.46 179.76 74.47 46 46.01 74.47 109.57 74.47 179.76S483.53 387.99 437.53 434c-46.01 46.01-109.57 74.47-179.76 74.47l-.08-.03-3.46.03zm-13.31-30.56V30.56C184.33 33.87 133.4 58.17 95.79 95.79c-40.55 40.54-65.62 96.56-65.62 158.44 0 61.89 25.07 117.91 65.62 158.45 37.61 37.61 88.54 61.91 145.13 65.23z"
        />
      </svg>
    </div>
  </div>
  <div
    v-else
    class="h-full min-h-[24px] border-l-[1px] text-center"
  >
    <svg
      v-if="(bookedMeals.booked.includes(meal.mealId) && !bookedCombinedMeal) || (bookedMeals.booked.includes(meal.mealId) && meal.title.en === 'Combined Dish')"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      class="text-primary m-auto block h-6 w-6"
    >
      <path
        d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm0,18a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z"
        fill="currentColor"
      />
      <path
        d="M14.7,8.39l-3.78,5L9.29,11.28a1,1,0,0,0-1.58,1.23l2.43,3.11a1,1,0,0,0,.79.38h0a1,1,0,0,0,.79-.39l4.57-6a1,1,0,1,0-1.6-1.22Z"
        fill="currentColor"
      />
    </svg>
    <svg
      v-if="bookedMeals.booked.includes(meal.mealId) && bookedCombinedMeal && meal.title.en !== 'Combined Dish'"
      xmlns="http://www.w3.org/2000/svg"
      fill-rule="evenodd"
      clip-rule="evenodd"
      viewBox="0 0 512 508.47"
      class="text-primary m-auto block h-6 w-6"
    >
      <path
        fill-rule="nonzero"
        fill="currentColor"
        d="M254.23 508.47c-3.94 0-7.87-.1-11.77-.28h-1.54v-.07c-64.9-3.34-123.37-31.04-166.45-74.12C28.46 387.99 0 324.42 0 254.23c0-70.19 28.46-133.75 74.47-179.76C117.55 31.39 176.03 3.69 240.92.34V.27h1.54c3.9-.18 7.83-.27 11.77-.27l3.46.02.08-.02c70.19 0 133.75 28.46 179.76 74.47 46 46.01 74.47 109.57 74.47 179.76S483.53 387.99 437.53 434c-46.01 46.01-109.57 74.47-179.76 74.47l-.08-.03-3.46.03zm-13.31-30.56V30.56C184.33 33.87 133.4 58.17 95.79 95.79c-40.55 40.54-65.62 96.56-65.62 158.44 0 61.89 25.07 117.91 65.62 158.45 37.61 37.61 88.54 61.91 145.13 65.23z"
      />
    </svg>
  </div>
</template>

<script setup lang="ts">
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';

defineProps<{
  bookedMeals: IBookedData,
  meal: IMealWithVariations,
  bookedCombinedMeal: boolean
}>();
</script>