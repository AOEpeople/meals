<template>
  <tr class="border-t-1 table w-full table-fixed border-b-2 first:border-t-0">
    <th
      :colspan="numberOfMeals"
      class="py-4 pl-4 text-left text-primary"
    >
      {{ slotName }}
    </th>
  </tr>
  <TransitionGroup
    name="rows"
    appear
  >
    <ParticipantsTableRow
      v-for="(bookedMeals, name) in participants"
      :key="name"
      :participant-name="name.toString()"
      :booked-meals="bookedMeals"
      :meals="meals"
    />
  </TransitionGroup>
</template>

<script setup lang="ts">
import { type Dictionary } from '@/types/types';
import ParticipantsTableRow from './ParticipantsTableRow.vue';
import { type IBookedData, type IMealWithVariations } from '@/api/getShowParticipations';
import { computed } from 'vue';

const props = defineProps<{
  slotName: string;
  participants: Dictionary<IBookedData>;
  meals: IMealWithVariations[];
}>();

const numberOfMeals = computed(() => {
  return props.meals.length + 1;
});
</script>

<style>
.rows-move,
.rows-enter-active,
.rows-leave-active {
  transition: all 0.5s ease;
}
.rows-enter-from,
.rows-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
