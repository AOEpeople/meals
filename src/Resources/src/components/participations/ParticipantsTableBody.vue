
import ParticipantsTableRow from './ParticipantsTableRow.vue';

<template>
  <tbody>
    <ParticipantsTableSlot
      v-for="(participants, slot) in participationsState.data"
      :key="slot"
      :slot-name="slot.toString()"
      :participats="participants"
    />
  </tbody>
</template>

<script setup lang="ts">

import { getShowParticipations } from '@/api/getShowParticipations';
import ParticipantsTableSlot from './ParticipantsTableSlot.vue';

const { participationsState } = getShowParticipations();
</script>