<template>
  <MealsList />
  <ParticipationsTable />
</template>


<script setup lang="ts">
import { usePrintableListData } from '@/api/getPrintableListData';
import { useI18n } from 'vue-i18n';
import MealsList from '@/components/participations/MealsList.vue';
import ParticipationsTable from '@/components/participations/ParticipationsTable.vue';



</script>