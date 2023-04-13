<template>
  <tr class="border-b-2 text-center">
    <td
      class="border-r-2 py-4"
    >
      {{ participantName }}
    </td>
    <td
      v-for="value in meals"
      :key="value.mealId"
      class="py-4"
    >
      <ParticipantsTableData
        :booked-meals="bookedMeals"
        :meal="value"
      />
    </td>
  </tr>
</template>

<script setup lang="ts">
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import ParticipantsTableData from './ParticipantsTableData.vue';

defineProps<{
  participantName: string,
  bookedMeals: IBookedData,
  meals: IMealWithVariations[]
}>();

</script>