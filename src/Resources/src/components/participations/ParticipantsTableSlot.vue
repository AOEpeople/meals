<template>
  <tr>
    <th :colspan="numberOfMeals">
      {{ slotName }}
    </th>
  </tr>
  <ParticipantsTableRow
    v-for="(participantData, name) in participats"
    :key="name"
    :participant-name="name.toString()"
    :meal-data="participantData"
  />
</template>

<script setup lang="ts">
import { Dictionary } from 'types/types';
import ParticipantsTableRow from './ParticipantsTableRow.vue';
import { computed } from 'vue';

const props = defineProps<{
  slotName: string,
  participats: Dictionary<Dictionary<boolean>>
}>();

const numberOfMeals = computed(() => {
  const firstParticipant = Object.keys(props.participats)[0];
  return Object.keys(props.participats[firstParticipant]).length + 2;
});
</script>