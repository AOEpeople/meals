<template>
  <tr class="border-b-2">
    <th
      :colspan="1"
      class="py-4"
    >
      {{ slotName }}
    </th>
  </tr>
  <ParticipantsTableRow
    v-for="(bookedMeals, name) in participants"
    :key="name"
    :participant-name="name.toString()"
    :booked-meals="bookedMeals"
    :meals="meals"
    class="mt-4"
  />
</template>

<script setup lang="ts">
import { Dictionary } from 'types/types';
import ParticipantsTableRow from './ParticipantsTableRow.vue';
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';

defineProps<{
  slotName: string,
  participants: Dictionary<IBookedData>,
  meals: IMealWithVariations[]
}>();

// const numberOfMeals = computed(() => {
//   return props.meals.length + 1;
// });
</script>