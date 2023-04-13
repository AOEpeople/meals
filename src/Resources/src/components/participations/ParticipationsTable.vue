<template>
  <table class="w-full table-fixed">
    <!-- <ParticipantsTableHead /> -->
    <ParticipantsTableTop />
    <ParticipantsTableBody />
  </table>
</template>


<script setup lang="ts">
import ParticipantsTableHead from './ParticipantsTableHead.vue';
import ParticipantsTableBody from './ParticipantsTableBody.vue';
import ParticipantsTableTop from './ParticipantsTableTop.vue';

</script>