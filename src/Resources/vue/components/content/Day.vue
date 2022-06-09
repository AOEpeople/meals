<template>
  <div class="flex rounded w-3/4 shadow-[0_15px_35px_0_#5B788F21] w-max-screen-aoe mx-auto h-[200px] bg-white">
    <div class="flex-none grid w-[24px] bg-primary-2 rounded-l-[5px]">

    </div>
    <div class="flex-1 grid grid-rows-3">
      <MealData :data="props.day.meal1" />
      <MealData :data="props.day.meal2" />
      <MealData :data="props.day.kombi" />
    </div>
  </div>
</template>

<script setup>
import MealData from "./day/MealData.vue";

const props = defineProps([
    'day',
])

</script>

<style>

</style>