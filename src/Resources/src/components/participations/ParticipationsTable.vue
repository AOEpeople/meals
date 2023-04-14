<template>
  <table class="flex h-[300px] w-full table-fixed flex-col">
    <!-- <ParticipantsTableTop class="top-0" /> -->
    <ParticipantsTableHead />
    <ParticipantsTableBody />
  </table>
</template>


<script setup lang="ts">
import ParticipantsTableBody from './ParticipantsTableBody.vue';
import ParticipantsTableHead from './ParticipantsTableHead.vue';

</script>