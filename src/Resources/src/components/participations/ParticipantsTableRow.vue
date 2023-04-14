<template>
  <tr class="table w-full table-fixed border-b-2 text-center">
    <td
      class="w-1/2 truncate border-r-2 py-4"
    >
      {{ participantName }}
    </td>
    <td
      v-for="value in meals"
      :key="value.mealId"
      class="py-4"
      :class="value.title.en === 'Combined Dish' ? 'w-1/2' : 'w-full'"
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