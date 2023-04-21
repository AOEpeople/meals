<template>
  <tr class="table w-full table-fixed border-b-2">
    <th
      :colspan="numberOfMeals"
      class="text-primary py-4 pl-4 text-left"
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
  />
</template>

<script setup lang="ts">
import { Dictionary } from 'types/types';
import ParticipantsTableRow from './ParticipantsTableRow.vue';
import { IBookedData, IMealWithVariations } from '@/api/getShowParticipations';
import { computed } from 'vue';

const props = defineProps<{
  slotName: string,
  participants: Dictionary<IBookedData>,
  meals: IMealWithVariations[]
}>();

const numberOfMeals = computed(() => {
  return props.meals.length + 1;
});
</script>