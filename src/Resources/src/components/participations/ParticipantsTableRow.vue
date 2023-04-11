<template>
  <tr class="text-center">
    <td>{{ participantName }}</td>
    <td>{{ combiBooked }}</td>
    <td
      v-for="(value, key) in mealData"
      :key="key"
    >
      {{ value }}
    </td>
  </tr>
</template>

<script setup lang="ts">
import { Dictionary } from 'types/types';
import { computed } from 'vue';

const props = defineProps<{
  participantName: string,
  mealData: Dictionary<boolean>
}>();

const combiBooked = computed(() => {
  let numberOfBookedMeals = 0;
  for(const booked of Object.values(props.mealData)) {
    if(booked) {
      numberOfBookedMeals++;
    }
  }
  return numberOfBookedMeals > 1;
});

</script>