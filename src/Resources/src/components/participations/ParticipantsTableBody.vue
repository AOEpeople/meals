<template>
  <tbody>
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

const { participationsState, getListOfBookableMeals, loadedState } = getShowParticipations();

const mealsWithVariations = computed(() => {
  if(loadedState.loaded && loadedState.error === "") {
    return getListOfBookableMeals();
  } else {
    return [];
  }
});
</script>