<template>
  <table
    class="flex w-full table-fixed flex-col"
    :style="{ height: tableHeight }"
  >
    <!-- <ParticipantsTableTop class="top-0" /> -->
    <ParticipantsTableHead id="tableHead" />
    <ParticipantsTableBody />
  </table>
</template>


<script setup lang="ts">
import { computed, onMounted } from 'vue';
import ParticipantsTableBody from './ParticipantsTableBody.vue';
import ParticipantsTableHead from './ParticipantsTableHead.vue';
import { useComponentHeights } from '@/services/useComponentHeights';

const { maxTableHeight, setTableHeaderId } = useComponentHeights();

const tableHeight = computed(() => {
  return `${maxTableHeight.value}px`;
});

onMounted(() => {
  setTableHeaderId('tableHead');
});
</script>