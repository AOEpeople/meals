<template>
  <tr class="text-center">
    <td>{{ participantName }}</td>
    <td
      v-for="value in meals"
      :key="value.mealId"
    >
      {{ bookedMeals.booked.includes(value.mealId) }}
    </td>
  </tr>
</template>

<script setup lang="ts">
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';

defineProps<{
  participantName: string,
  bookedMeals: IBookedData,
  meals: IMealWithVariations[]
}>();

</script>