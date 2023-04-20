<template>
  <div
    v-if="meal.variations.length > 0"
    class="flex flex-row items-stretch"
  >
    <div
      v-for="(variation, index) in meal.variations"
      :key="variation.mealId"
      class="flex-1 border-l-2 text-center"
    >
      <svg
        v-if="bookedMeals.booked.includes(variation.mealId)"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        class="text-primary m-auto block h-6 w-6"
      >
        <path
          fill-rule="evenodd"
          d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z"
          clip-rule="evenodd"
        />
      </svg>
    </div>
  </div>
  <div
    v-else
    class="h-full min-h-[24px] border-l-2 text-center"
  >
    <svg
      v-if="bookedMeals.booked.includes(meal.mealId)"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 24 24"
      fill="currentColor"
      class="text-primary m-auto block h-6 w-6"
    >
      <path
        fill-rule="evenodd"
        d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z"
        clip-rule="evenodd"
      />
    </svg>
  </div>
</template>

<script setup lang="ts">
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';

defineProps<{
  bookedMeals: IBookedData,
  meal: IMealWithVariations
}>();
</script>