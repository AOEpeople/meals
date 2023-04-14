<template>
  <tbody class="grow-1 shrink-1 block basis-auto overflow-y-auto overflow-x-hidden py-4">
    <ParticipantsTableSlot
      v-for="(participants, slot) in participationsState.data"
      :key="slot"
      :slot-name="slot.toString()"
      :participants="participants"
      :meals="mealsWithVariations"
    />
  </tbody>
</template>

<script setup lang="ts">

import { getShowParticipations } from '@/api/getShowParticipations';
import ParticipantsTableSlot from './ParticipantsTableSlot.vue';
import { computed } from 'vue';

const { participationsState, getMealsWithVariations, loadedState } = getShowParticipations();

const mealsWithVariations = computed(() => {
  if(loadedState.loaded && loadedState.error === "") {
    return getMealsWithVariations();
  } else {
    return [];
  }
});
</script>